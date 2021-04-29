<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ControllerExtensionModuleFacebookBusiness extends Controller {
    private $facebook_app_id = '785409108588782';
    private $facebook_feed_filename = 'fbe_product_catalog.csv';

    public function index() {
    }

    public function genFeed($gen_now = false) {
        $this->load->model('catalog/product');
        $this->load->model('extension/module/facebook_business');
        $this->load->model('localisation/currency');

        $start_time = time();

        try {
            $product_feed_path = $this->getProductFeedPath();

            if ($product_feed_path) {
                $is_stale = $this->isFeedFileStale($product_feed_path);

                if (!$is_stale && !$gen_now) {
                    // log skip generation of feed
                } else {
                    // remove the file if it exists
                    // this is because we are switching to append mode
                    // when writing the file and to avoid writing into existing content
                    if (is_file($product_feed_path)) {
                        unlink($product_feed_path);
                    }

                    if (!$this->generateProductFeedFile($product_feed_path)) {
                        return false;
                    }
            
                    // performs a last check if the feed file is successfully generated
                    if (is_file($product_feed_path)) {
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }

            $end_time = time();
            $feed_gen_time = $end_time - $start_time;
        
            $this->estimateFeedGenerationTimeWithDecay($feed_gen_time);
      
            // genFeedPing return time estimation only
            if (isset($this->request->get['from']) && $this->request->get['from'] == 'genFeedPing') {
                return;
            }
      
            $this->sendFileResponse($product_feed_path);
        } catch (Exception $e) {
            $this->response->addHeader('Content-type: text');
            $this->response->setOutput('There was a problem generating your feed: %s', $e->getMessage());
        }
    }

    public function genFeedNow() {
        $this->genFeed(true);
    }

    public function genFeedPing() {
        $this->load->model('catalog/product');
        $this->load->model('extension/module/facebook_business');

        $time = $this->estimateFeedGenerationTime();
        $this->response->addHeader('Content-type: text/plain');
        $this->response->setOutput(round($time));

        // This will call the genAction method above in an async request
        // so that we can still return a response from the ping action.
        try {
            $url = HTTP_SERVER.'index.php?route=extension/module/facebook_business/genFeed&from=genFeedPing';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            // We expect the result to time out.
        }
    }

    private function estimateFeedGenerationTime() {
        $product_total = $this->model_catalog_product->getTotalProducts();

        $num_of_samples = $product_total <= 500 ? $product_total : 500;

        if ($num_of_samples == 0) {
            return 30;
        }

        $feed_dryrun_filename = $this->getWritableProductFeedDir() . 'fbe_feed_dryrun.txt';
        $feed_dryrun_file = fopen($feed_dryrun_filename, 'ab');
        
        $start_time = time();
        $this->writeProductFeedFileInBatch($num_of_samples, $feed_dryrun_file);
        $end_time = time();

        $time_spent = $end_time - $start_time;

        $time_estimate = $time_spent * $product_total / $num_of_samples * 1.5 + 30;

        $time_previous_avg = $this->config->get('facebook_feed_runtime_avg');

        if (!$time_previous_avg) {
            $time_previous_avg = 0;
        }

        return max($time_estimate, $time_previous_avg);
    }
  

    private function estimateFeedGenerationTimeWithDecay($feed_gen_time) {
        // Update feed generation online time estimate w/ 25% decay.
        $old_feed_gen_time = $this->config->get('facebook_feed_runtime_avg');

        if (!$old_feed_gen_time) {
            $old_feed_gen_time = 0;
        }

        if ($feed_gen_time < $old_feed_gen_time) {
            $feed_gen_time = $feed_gen_time * 0.25 + $old_feed_gen_time * 0.75;
        }

        $data = array(
            'facebook_feed_runtime_avg' => $feed_gen_time
        );

        $this->model_extension_module_facebook_business->updateFacebookSettings($data);
    }

    private function sendFileResponse($filename) {
        if (!headers_sent()) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="'.basename($filename.'"'));
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length:'.filesize($filename));
      
            if (ob_get_level()) {
                ob_end_clean();
            }
      
            readfile($filename, 'rb');
      
            exit();
        }
    }

    private function getProductFeedPath() {
        $product_feed_dir = $this->getWritableProductFeedDir();

        if (!$product_feed_dir) {
            return false;
        }

        return $product_feed_dir . $this->facebook_feed_filename;
    }

    private function getWritableProductFeedDir() {
        // Checks on 3 folders if they are writable and return a folder if so
        if (is_writable(DIR_DOWNLOAD)) {
            return DIR_DOWNLOAD;
        }

        if (is_writable(DIR_MODIFICATION)) {
            return DIR_MODIFICATION;
        }

        if (is_writable(DIR_LOGS)) {
            return DIR_LOGS;
        }

        return false;
    }

    private function isFeedFileStale($filepath) {
        $time_file_modified = file_exists($filepath) ? filemtime($filepath) : 0;
    
        // if we get no file modified time, or the modified time is 8hours ago,
        // we count it as stale
        if (!$time_file_modified) {
            return true;
        } else {
            return time() - $time_file_modified > 8*3600;
        }
    }

    private function generateProductFeedFile($product_feed_path) {
        try {
            // opens up the feed file and close inside the main method
            // to avoid the extra overhead of file opening and closing
            // error_log('feed file = ' . $product_feed_path);
            $feed_file = fopen($product_feed_path, "ab");
      
            if (!$this->writeProductFeedFileHeader($feed_file)) {
                // something wrong happened, return false
                fclose($feed_file);
                return false;
            }
            // queries and writes the products in batches
            // this is to handle for large product catalogs
            $filter_data = array(
                'filter_status' => 1
            );

            $product_total = $this->model_catalog_product->getTotalProducts($filter_data);
      
            return $this->writeProductFeedFileInBatch($product_total, $feed_file);
        } catch (Exception $e) {
            // handles any exceptions during the feed file generation
            if (isset($feed_file) && !!($feed_file)) {
                fclose($feed_file);
            }

            return false;
        }
    }

    private function writeProductFeedFileHeader($feed_file) {
        $product_feed_header_row = 'id,title,description,image_link,link,google_product_category,brand,price,' . 
            'availability,item_group_id,additional_image_link,sale_price,sale_price_effective_date,condition,' . 
            'age_group,color,gender,material,pattern' . PHP_EOL;
        try {
            fputs($feed_file, "\xEF\xBB\xBF");
            fwrite($feed_file, $product_feed_header_row);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function writeProductFeedFileInBatch($product_total, $feed_file) {
        $total_batches = ceil($product_total / 100);
        for ($batch_number = 0; $batch_number < $total_batches; $batch_number++) {
            $filter_data = array(
                'start' => $batch_number * 100,
                'limit' => 100,
                'filter_status' => 1
            );

            $products = $this->model_extension_module_facebook_business->getProducts($filter_data);

            if (isset($products) && sizeof($products) > 0) {
                if (!$this->writeProductFeedFile($products, $feed_file)) {
                    // something wrong happened, return false
                    fclose($feed_file);
                    return false;
                }
            }
        }

        // feed file is generated successfully
        fclose($feed_file);
        return true;
    }

    private function writeProductFeedFile($products, $feed_file) {
        $product_data = array();

        try {
            foreach ($products as $product) {
                $formatted_product_data = $this->formatProductDetails($product);

                if ($formatted_product_data) {
                    fwrite($feed_file, $this->convertProductDataAsFeedRow($formatted_product_data));
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function convertProductDataAsFeedRow($product_data) {
        $row = '';

        $count = count($product_data);

        foreach ($product_data as $product) {
            $count--;

            if ($count == 0) {
                $row .= $product . PHP_EOL;
            } else {
                $row .= $product . ',';
            }
        }

        return $row;
    }

    private function formatAndTrimString($text, $length = false) {
        if ($text) {
            $text = trim(strip_tags(html_entity_decode(html_entity_decode($text), ENT_QUOTES | ENT_COMPAT, 'UTF-8')));

            if ($length && strlen($text) > $length) {
                $text = substr($text, 0, $length);
            }

            $text = '"' . str_replace('"', '""', $text) . '"';

            return $text;
        } else {
            return '""';
        }
    }

    private function getStoreBaseUrl() {
        if ($this->config->get('config_ssl')) {
            return HTTP_SERVER;
        } else {
            return HTTPS_SERVER;
        }
    }

    private function formatProductDetails($product_info) {
        $special_info = $this->model_extension_module_facebook_business->getProductSpecials($product_info['product_id']);

        if ($special_info) {
            $product_info = array_merge($product_info, $special_info);
        }

        $formatted_product_details = array(
            'retailer_id'                 => $product_info['product_id'],
            'name'                        => $this->getName($product_info),
            'description'                 => $this->getDescription($product_info),
            'image_url'                   => $this->formatAndTrimString($this->getImageUrl($product_info['image'])),
            'product_url'                 => $this->getProductUrl($product_info['product_id']),
            'category'                    => $this->getCategory($product_info),
            'brand'                       => $this->getBrand($product_info),
            'price'                       => $this->getPrice($product_info),
            'availability'                => $this->getAvailability($product_info),
            'retailer_product_group_id'   => $product_info['product_id'],
            'additional_image_urls'       => $this->getAdditionalImageUrls($product_info['product_id']),
            'special'                     => $this->getSpecialPrice($product_info),
            'special_period'              => $this->getSpecialPricePeriod($product_info),
            'condition'                   => $this->getCondition($product_info['product_id'])
        );

        $additional_details = $this->getAdditionalDetails($product_info['product_id']);
        $formatted_product_details = array_merge($formatted_product_details, $additional_details);

        // Ensure that the product checks all requirements for the product feed
        if (in_array(false, $formatted_product_details, true) === true) {
            return false;
        } else {
            return $formatted_product_details;
        }
    }

    private function getName($product_info) {
        return $this->formatAndTrimString($product_info['name'], 150);
    }

    private function getDescription($product_info) {
        $description = $this->formatAndTrimString($product_info['description'], 5000);

        // Fallback to Meta Description if description is not available
        if (!$description) {
            $description = $this->formatAndTrimString($product_info['meta_description'], 5000);
        }

        // Fallback to Product Name if Description and Meta Description are not available
        if (!$description) {
            $description = $this->formatAndTrimString($product_info['name'], 5000);
        }

        // Check if description length is less than 30 characters
        if (strlen($description) < 30) {
            return false;
        }

        // If description doesn't contain non-English characters, check if all Uppercase
        if (strlen($description) == strlen(utf8_decode($description))) {
            if (strtoupper($description) == $description) {
                $description = ucfirst(strtolower($description));
            }
        }

        return $description;
    }

    private function getImageUrl($image) {
        // Cater for cases where the image is an external URL
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        } else {
            return $this->getStoreBaseUrl() . 'image/' . $image;
        }
    }

    private function getAdditionalImageUrls($product_id) {
        $formatted_images = '';

        $product_images = $this->model_catalog_product->getProductImages($product_id);

        // Limit of up to 20 images
        $product_images = array_slice($product_images, 0, 20);

        foreach ($product_images as $product_image) {
            $image_url = $this->getImageUrl($product_image['image']);

            // Limit of up to 2000 characters
            if (strlen($formatted_images . $image_url) < 2000) {
                if ($formatted_images) {
                    $formatted_images .= ',' . $image_url;
                } else {
                    $formatted_images .= $image_url;
                }
            } else {
                break;
            }
        }

        return $this->formatAndTrimString($formatted_images);
    }

    private function getProductUrl($product_id) {
        $product_url = $this->url->link('product/product', 'product_id=' . (int)$product_id, true);

        return $this->formatAndTrimString($product_url);
    }

    private function getCategory($product_info) {
        $product_to_facebook = $this->model_extension_module_facebook_business->getProductToFacebook($product_info['product_id']);

        if ($product_to_facebook['google_product_category']) {
            return $product_to_facebook['google_product_category'];
        } else {
            $category = $product_info['category_name'];

            if (!$category) {
                $category = $this->config->get('config_name');
            }
    
            return $this->formatAndTrimString($category);
        }
    }

    private function getBrand($product_info) {
        if ($product_info['manufacturer_name']) {
            $brand = $product_info['manufacturer_name'];
        } else {
            $brand = $this->config->get('config_name');
        }

        return $this->formatAndTrimString($brand);
    }

    private function getPrice($product_info) {
        if ($product_info['tax_class_id']) {
            $price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
        } else {
            $price = $product_info['price'];
        }

        $price = number_format(round((float)$price, 2), 2, '.', '');
        $price = $price . ' ' . strtoupper($this->config->get('config_currency'));

        return $price;
    }

    private function getSpecialPrice($product_info) {
        if (!empty($product_info['special']) && $this->config->get('facebook_business_sync_specials_status')) {
            if ($product_info['tax_class_id']) {
                $special = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
            } else {
                $special = $product_info['special'];
            }

            $special = number_format(round((float)$special, 2), 2, '.', '');
            $special = $special . ' ' . strtoupper($this->config->get('config_currency'));
        } else {
            $special = '""';
        }

        return $special;
    }

    private function getSpecialPricePeriod($product_info) {
        if (!empty($product_info['special']) && $this->config->get('facebook_business_sync_specials_status') && !empty($product_info['special_date_start']) && !empty($product_info['special_date_end'])
          && $product_info['special_date_start'] != '0000-00-00' && $product_info['special_date_end'] !='0000-00-00') {
            $datetime_start = new DateTime($product_info['special_date_start']);
            $datetime_end = new DateTime($product_info['special_date_end']);
            $special_date_start = $datetime_start->format('Y-m-d') . 'T00:00+00:00';
            $special_date_end = $datetime_end->format('Y-m-d') . 'T23:59+00:00';

            $special_price_period = $special_date_start . '/' . $special_date_end;

            return $this->formatAndTrimString($special_price_period);
        } else {
            return '""';
        }
    }

    public function getAvailability($product_info) {
        if ($product_info['quantity'] <= 0 && $product_info['subtract']) {
            $availability = 'out of stock';
        } else {
            $availability = 'in stock';
        }
    
        return $availability;
    }

    private function getCondition($product_id) {
        $product_to_facebook = $this->model_extension_module_facebook_business->getProductToFacebook($product_id);

        if ($product_to_facebook) {
            if ($product_to_facebook['condition']) {
                return $product_to_facebook['condition'];
            }
        }

        return 'new';
    }

    private function getAdditionalDetails($product_id) {
        $additional_details = array();

        $product_to_facebook = $this->model_extension_module_facebook_business->getProductToFacebook($product_id);

        if ($product_to_facebook) {
            foreach ($product_to_facebook as $key => $value) {
                if ($key == 'google_product_category' || $key == 'condition') {
                    continue;
                } else {
                    if ($value) {
                        $additional_details[$key] = $this->formatAndTrimString($value);
                    } else {
                        $additional_details[$key] = '';
                    }
                }
            }
        }

        return $additional_details;
    }

    public function getProductInfoForFacebookPixel() {
        $event_name = (isset($this->request->get['event_name']))
          ? $this->request->get['event_name']
          : '';
    
        // creating a default facebook_pixel_params with just the event_name
        // and empty parameters
        // this is to guard against cases
        // where the product is not found
        // or the product_id is not available
        $facebook_pixel_event_params = array('event_name' => $event_name);

        if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
            $this->load->model('catalog/product');
            $this->load->model('extension/module/facebook_business');

            $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

            if ($product_info) {
                $quantity = isset($this->request->get['quantity']) ? $this->request->get['quantity'] : 1;

                $contents = array(
                    'id'        => $product_info['product_id'],
                    'quantity'  => $quantity
                );

                if ((float)$product_info['special'] && $this->config->get('facebook_business_sync_specials_status')) {
                    $price = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                } else {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                }

                $event_id = $this->model_extension_module_facebook_business->generateEventId();

                $facebook_pixel_event_params = array(
                    'event_name'    => $event_name,
                    'content_ids'   => array((string)$product_info['product_id']),
                    'content_name'  => $this->model_extension_module_facebook_business->formatString($product_info['name']),
                    'content_type'  => 'product',
                    'contents'      => array($contents),
                    'currency'      => strtoupper($this->session->data['currency']),
                    'value'         => $price,
                    'event_id'      => $event_id
                );

                $this->model_extension_module_facebook_business->trackPixel($facebook_pixel_event_params, $event_name, $event_id);
            }
        }

        $json = array('facebook_pixel_event_params' => $facebook_pixel_event_params);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function eventPreViewCommonHeader(&$route, &$data) {
        $this->load->model('extension/module/facebook_business');

        $data['facebook_page_id'] = $this->config->get('facebook_page_id');
        $data['facebook_jssdk_version'] = $this->config->get('facebook_jssdk_version');
        $data['facebook_messenger_enabled'] = $this->config->get('facebook_messenger_activated');
        
        if ($this->config->get('facebook_customization_locale')) {
            $data['facebook_customization_locale'] = $this->config->get('facebook_customization_locale');
        } else {
            $data['facebook_customization_locale'] = 'en_US';
        }

        // Retrieve latest settings
        $this->model_extension_module_facebook_business->updateUseS2SUsePIIByAAMSetting();

        $data['facebook_pixel_id'] = $this->config->get('facebook_pixel_id');
        $data['facebook_pixel_pii'] = $this->model_extension_module_facebook_business->getPii();
        $data['facebook_pixel_params'] = $this->model_extension_module_facebook_business->getAgentParameters();
        $data['facebook_pixel_event_params'] = $this->model_extension_module_facebook_business->getEventParameters();
        $data['facebook_cookie_bar_status'] = $this->config->get('facebook_business_cookie_bar_status');

        $data['cookie_bar_header']      = 'Our Site Uses Cookies';
        $data['cookie_bar_description'] = 'By clicking Agree, you agree to our <a class="cc-link" href="https://www.facebook.com/legal/terms/update" target="_blank">terms of service</a>, <a class="cc-link" href="https://www.facebook.com/policies/" target="_blank">privacy policy</a> and <a class="cc-link" href="https://www.facebook.com/policies/cookies/" target="_blank">cookies policy</a>.';
        $data['cookie_bar_opt_in']      = 'Agree';
        $data['cookie_bar_opt_out']     = 'Opt Out';
    }

    public function eventPostViewCommonHeader(&$route, &$data, &$output) {
        $html = $this->load->view('extension/module/facebook_business', $data);
        $html .= '</head>';

        $output = str_replace('</head>', $html, $output);
    }

    public function eventPostModelAddOrder($route, &$data, $order_id) {
        if ($order_id) {
            $this->session->data['facebook_business_order_id'] = $order_id;
        }
    }
}