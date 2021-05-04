<?php
/**
  * Copyright (c) Facebook, Inc. and its affiliates.
  * All rights reserved.
  *
  * This source code is licensed under the license found in the
  * LICENSE file in the root directory of this source tree.
  */

require_once(DIR_SYSTEM . 'library/vendor/facebook_business/vendor/autoload.php');

use FacebookAds\Object\ServerSide\AdsPixelSettings;

class ControllerExtensionModuleFacebookBusiness extends Controller {
    private $error = array();

    private $opencart_server_base_url = 'https://opencart-plugin.com';
    private $facebook_app_id = '785409108588782';

    public function index() {
        $data = $this->load->language('extension/module/facebook_business');

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('facebook_business', $this->request->post);

            $this->clearProductFeed();

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
        }

        $this->load->model('extension/module/facebook_business');

        $plugin_version = $this->model_extension_module_facebook_business->getPluginVersion();

        $data['opencart_iframe_url'] = $this->opencart_server_base_url . '/facebook?'
        . 'external_business_id=' . urlencode(HTTPS_CATALOG)
        . '&business_name=' . addslashes($this->config->get('config_name'))
        . '&feed_url=' . urlencode(HTTPS_CATALOG . 'index.php?route=extension/module/facebook_business/genFeed')
        . '&feed_ping_url=' . urlencode(HTTPS_CATALOG . 'index.php?route=extension/module/facebook_business/genFeedPing')
        . '&timezone=' . date_default_timezone_get()
        . '&currency=' . strtoupper(addslashes($this->config->get('config_currency')))
        . '&version=' . $plugin_version;

        if ($this->config->get('config_maintenance')) {
            $data['error_maintenance_mode'] = $this->language->get('error_maintenance_mode');
        } else {
            $data['error_maintenance_mode'] = '';
        }

        if (!empty($this->config->get('facebook_dia_setting_id'))) {
            $data['opencart_iframe_url'] .= '&merchant_settings_id=' . $this->config->get('facebook_dia_setting_id');
        }

        if (!empty($this->config->get('facebook_fbe_v2_installed'))) {
            $data['opencart_iframe_url'] .= '&fbe_v2_installed=' . $this->config->get('facebook_fbe_v2_installed');
        }

        if (!empty($this->config->get('facebook_use_s2s'))) {
            $data['opencart_iframe_url'] .= '&s2s_configured=' . $this->config->get('facebook_use_s2s');
        }

        $data['opencart_server_base_url'] = $this->opencart_server_base_url;
        $data['facebook_app_id'] = $this->facebook_app_id;
        $data['token'] = $this->session->data['token'];

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/facebook_business', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/module/facebook_business', 'token=' . $this->session->data['token'], true);

        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->request->post['facebook_business_cookie_bar_status'])) {
            $data['facebook_business_cookie_bar_status'] = $this->request->post['facebook_business_cookie_bar_status'];
        } else {
            $data['facebook_business_cookie_bar_status'] = $this->config->get('facebook_business_cookie_bar_status');
        }

        if (isset($this->request->post['facebook_business_sync_specials_status'])) {
            $data['facebook_business_sync_specials_status'] = $this->request->post['facebook_business_sync_specials_status'];
        } else {
            $data['facebook_business_sync_specials_status'] = $this->config->get('facebook_business_sync_specials_status');
        }

        $data['text_plugin_version'] = sprintf($this->language->get('text_plugin_version'), $plugin_version);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/facebook_business', $data));
    }

    public function productPage() {
        $data = $this->load->language('extension/module/facebook_business');

        $this->load->model('extension/module/facebook_business');

        // Get Google Product Categories
        $google_taxonomy_url = 'https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt';

        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true
        );

        $curl = curl_init($google_taxonomy_url);
        curl_setopt_array($curl, $curl_options);
        $content = curl_exec($curl);

        if (curl_errno($curl)) {
            $curl_error = curl_error($curl);
        } else {
            $curl_error = false;
        }

        curl_close($curl);

        $data['google_product_categories'] = array();
        $data['error_google_product_category'] = '';

        if ($content && !$curl_error) {
            $google_product_categories_file = explode(PHP_EOL, $content);
            $version = array_shift($google_product_categories_file);
            
            foreach ($google_product_categories_file as $google_product_category) {
                if ($google_product_category) {
                    list($id, $name) = explode(' - ', $google_product_category);
                    $data['google_product_categories'][$id] = $name;
                }
            }
        } else {
            $data['error_google_product_category'] = $this->language->get('error_google_product_category');
        }

        if (isset($this->request->get['product_id'])) {
            $facebook_params = $this->model_extension_module_facebook_business->getFacebookParams($this->request->get['product_id']);
        } else {
            $facebook_params = array(
                'facebook_google_product_category' => '',
                'facebook_condition'               => '',
                'facebook_age_group'               => '',
                'facebook_color'                   => '',
                'facebook_gender'                  => '',
                'facebook_material'                => '',
                'facebook_pattern'                 => ''
            );
        }

        foreach ($facebook_params as $facebook_param => $value) {
            if (isset($this->request->post[$facebook_param])) {
                $data[$facebook_param] = $this->request->post[$facebook_param];
            } else {
                $data[$facebook_param] = $value;
            }
        }

        return $data;
    }

    public function install() {
        if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
            return;
        }

        $this->load->model('extension/module/facebook_business');

        $this->model_extension_module_facebook_business->install();

        $this->load->model('extension/event');
        
        $this->model_extension_event->addEvent('facebook_business', 'admin/view/common/dashboard/after', 'extension/module/facebook_business/eventPostViewCommonDashboard');
        $this->model_extension_event->addEvent('facebook_business', 'admin/view/common/column_left/before', 'extension/module/facebook_business/eventPreViewCommonColumnLeft');
        $this->model_extension_event->addEvent('facebook_business', 'admin/view/catalog/product_form/before', 'extension/module/facebook_business/eventPreViewCatalogProductForm');
        $this->model_extension_event->addEvent('facebook_business', 'admin/view/catalog/product_form/after', 'extension/module/facebook_business/eventPostViewCatalogProductForm');
        $this->model_extension_event->addEvent('facebook_business', 'admin/model/catalog/product/addProduct/after', 'extension/module/facebook_business/eventPostModelAddProduct');
        $this->model_extension_event->addEvent('facebook_business', 'admin/model/catalog/product/editProduct/after', 'extension/module/facebook_business/eventPostModelEditProduct');
        $this->model_extension_event->addEvent('facebook_business', 'admin/model/catalog/product/copyProduct/after', 'extension/module/facebook_business/eventPostModelCopyProduct');
        $this->model_extension_event->addEvent('facebook_business', 'admin/model/catalog/product/deleteProduct/after', 'extension/module/facebook_business/eventPostModelDeleteProduct');
        $this->model_extension_event->addEvent('facebook_business', 'catalog/view/common/header/before', 'extension/module/facebook_business/eventPreViewCommonHeader');
        $this->model_extension_event->addEvent('facebook_business', 'catalog/view/*/common/header/after', 'extension/module/facebook_business/eventPostViewCommonHeader');
        $this->model_extension_event->addEvent('facebook_business', 'catalog/model/checkout/order/addOrder/after', 'extension/module/facebook_business/eventPostModelAddOrder');
    }

    public function uninstall() {
        if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
            return;
        }

        $this->load->model('extension/module/facebook_business');

        $this->model_extension_module_facebook_business->uninstall();

        $this->load->model('extension/event');
        
        $this->model_extension_event->deleteEvent('facebook_business');
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/facebook_business')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function updateSettings() {
        $json = array();

        $this->load->model('extension/module/facebook_business');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['facebook_pixel_id'])) {
                $this->request->post['facebook_use_s2s'] = true;
                $this->request->post['facebook_pixel_use_pii'] = $this->getPixelAAMSettings($this->request->post['facebook_pixel_id']);
                $this->request->post['facebook_pixel_enabled_aam_fields'] = $this->getPixelEnabledAAMFields($this->request->post['facebook_pixel_id']);
                $this->request->post['facebook_last_aam_check_time'] = time();
            }

            $this->model_extension_module_facebook_business->updateFacebookSettings($this->request->post);

            $json['success'] = true;
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteSettings() {
        $json = array();

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->deleteSetting('facebook');

            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getPixelAAMSettings($pixel_id) {
        $settings = AdsPixelSettings::buildFromPixelId($pixel_id);

        if ($settings !== null) {
          return $settings->getEnableAutomaticMatching() ? 'true' : 'false';
        }

        return 'false';
    }

    private function getPixelEnabledAAMFields($pixel_id) {
        $settings = AdsPixelSettings::buildFromPixelId($pixel_id);

        if ($settings !== null) {
          $enabled_aam_fileds = $settings->getEnabledAutomaticMatchingFields();
          return implode(',', $enabled_aam_fileds);
        }

        return '';
    }

    public function eventPostViewCommonDashboard(&$route, &$data, &$output) {
        $this->load->language('extension/module/facebook_business');
        $this->load->model('extension/module/facebook_business');

        if ($this->model_extension_module_facebook_business->isNewExtensionAvailable()) {
            $search = '<div class="container-fluid">';

            $pos = strrpos($output, $search);

            if ($pos !== false) {
                $html =  '<div class="container-fluid">';
                $html .= '  <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> ' . $this->language->get('text_upgrade_message');
                $html .= '    <button type="button" class="close" data-dismiss="alert">&times;</button>';
                $html .= '  </div>';

                $output = substr_replace($output, $html, $pos, strlen($search));
            }
        }
    }

    public function eventPostViewCatalogProductForm($route, &$data, &$output) {
        $html = '<li><a href="#tab-facebook" data-toggle="tab">' . $data['tab_facebook'] . '</a></li>';
        $html .= '<li><a href="#tab-design" data-toggle="tab">';

        $output = str_replace('<li><a href="#tab-design" data-toggle="tab">', $html, $output);

        $html = $this->load->view('extension/module/facebook_business_product', $data);
        $html .= '<div class="tab-pane" id="tab-design">';

        $output = str_replace('<div class="tab-pane" id="tab-design">', $html, $output);
    }


    public function eventPreviewCommonColumnLeft(&$route, &$data) {
        $facebook_business_menu = array(
            'id'       => 'menu-facebook-business',
            'icon'     => 'fa-facebook-square',
            'name'     => 'Facebook Business Extension',
            'href'     => $this->url->link('extension/module/facebook_business', 'token=' . $this->session->data['token'], true),
            'children' => array()
        );

        array_unshift($data['menus'], $facebook_business_menu);
    }

    public function eventPreViewCatalogProductForm($route, &$data) {
        $data = array_merge($this->load->controller('extension/module/facebook_business/productPage'), $data);
    }

    public function eventPostModelAddProduct($route, &$args, $product_id) {
        $data = $args[0];

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_facebook WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_facebook SET product_id = '" . (int)$product_id . "', google_product_category = '" . (int)$data['facebook_google_product_category'] . "', `condition` = '" . $this->db->escape($data['facebook_condition']) . "', age_group = '" . $this->db->escape($data['facebook_age_group']) . "', color = '" . $this->db->escape($data['facebook_color']) . "', gender = '" . $this->db->escape($data['facebook_gender']) . "', material = '" . $this->db->escape($data['facebook_material']) . "', pattern = '" . $this->db->escape($data['facebook_pattern']) . "'");
    }

    public function eventPostModelCopyProduct($route, &$args, $test) {
        $product_id = $args[0];

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_facebook` WHERE product_id = '" . (int)$product_id . "'");

        if ($query->num_rows) {
            $facebook_params = array(
                'facebook_google_product_category'   => $query->row['google_product_category'],
                'facebook_condition'                 => $query->row['condition'],
                'facebook_age_group'                 => $query->row['age_group'],
                'facebook_color'                     => $query->row['color'],
                'facebook_gender'                    => $query->row['gender'],
                'facebook_material'                  => $query->row['material'],
                'facebook_pattern'                   => $query->row['pattern']
            );
        } else {
            $facebook_params = array(
                'facebook_google_product_category'   => '',
                'facebook_condition'                 => '',
                'facebook_age_group'                 => '',
                'facebook_color'                     => '',
                'facebook_gender'                    => '',
                'facebook_material'                  => '',
                'facebook_pattern'                   => ''
            );
        }

        $last_id_query = $this->db->query("SELECT MAX(product_id) AS `product_id` FROM " . DB_PREFIX . "product");

        if ($last_id_query->row['product_id']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_facebook SET product_id = '" . (int)$last_id_query->row['product_id'] . "', google_product_category = '" . (int)$facebook_params['facebook_google_product_category'] . "', `condition` = '" . $this->db->escape($facebook_params['facebook_condition']) . "', age_group = '" . $this->db->escape($facebook_params['facebook_age_group']) . "', color = '" . $this->db->escape($facebook_params['facebook_color']) . "', gender = '" . $this->db->escape($facebook_params['facebook_gender']) . "', material = '" . $this->db->escape($facebook_params['facebook_material']) . "', pattern = '" . $this->db->escape($facebook_params['facebook_pattern']) . "'");
        }
    }

    public function eventPostModelEditProduct($route, &$args) {
        $product_id = $args[0];
        $data = $args[1];

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_facebook WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_facebook SET product_id = '" . (int)$product_id . "', google_product_category = '" . (int)$data['facebook_google_product_category'] . "', `condition` = '" . $this->db->escape($data['facebook_condition']) . "', age_group = '" . $this->db->escape($data['facebook_age_group']) . "', color = '" . $this->db->escape($data['facebook_color']) . "', gender = '" . $this->db->escape($data['facebook_gender']) . "', material = '" . $this->db->escape($data['facebook_material']) . "', pattern = '" . $this->db->escape($data['facebook_pattern']) . "'");
    }

    public function eventPostModelDeleteProduct($route, &$args) {
        $product_id = $args[0];

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_facebook WHERE product_id = '" . (int)$product_id . "'");
    }

    private function clearProductFeed() {
        $feed_file_dir = '';

        if (is_writable(DIR_DOWNLOAD)) {
            $feed_file_dir = DIR_DOWNLOAD;
        } elseif (is_writable(DIR_MODIFICATION)) {
            $feed_file_dir = DIR_MODIFICATION;
        } elseif (is_writable(DIR_LOGS)) {
            $feed_file_dir = DIR_LOGS;
        }

        if ($feed_file_dir) {
            $product_feed_path = $feed_file_dir . 'fbe_product_catalog.csv';

            if (is_file($product_feed_path)) {
                unlink($product_feed_path);
            }
        }
    }
}