<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

require_once(DIR_SYSTEM . 'library/vendor/facebook_business/vendor/autoload.php');

use FacebookAds\Api;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\AdsPixelSettings;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequestAsync;
use FacebookAds\Object\ServerSide\UserData;
use FacebookAds\Object\ServerSide\Util;

class ModelModuleFacebookBusiness extends Model {
    private $pluginVersion = '4.0.0';

    // this function is a direct lifting from admin/model/catalog/product.php
    // except that the SQL query is joining other tables to obtain
    // brand, category, facebook_product_id and facebook_product_group_id
    // the rational to duplicate this method into this external class
    // instead of modifying the existing method which may lead to
    // breakage with other 3rd party plugins
    public function getProducts($data = array()) {
        $sql = "SELECT p.*, pd.*, m.name AS manufacturer_name, ptc.category_name FROM " . DB_PREFIX . "product p " .
          "LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) " .
          "LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) " .
          "LEFT JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id) " .
          "LEFT JOIN " .
            "(SELECT ptc.product_id, ptc.category_id, cd.name AS category_name " .
              "FROM (SELECT product_id, MAX(category_id) AS category_id " .
                "FROM " . DB_PREFIX . "product_to_category " .
                "GROUP BY product_id) AS ptc " .
              "LEFT JOIN " . DB_PREFIX . "category_description cd " .
                "ON (ptc.category_id = cd.category_id)) ptc " .
            "ON (p.product_id = ptc.product_id) " .
          "WHERE pd.language_id = '" .
            (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
            $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
        }

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
            $sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_image']) && !is_null($data['filter_image'])) {
            if ($data['filter_image'] == 1) {
                $sql .= " AND (p.image IS NOT NULL AND p.image <> '' AND p.image <> 'no_image.png')";
            } else {
                $sql .= " AND (p.image IS NULL OR p.image = '' OR p.image = 'no_image.png')";
            }
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getProductSpecials($product_id) {
        $query = $this->db->query("SELECT price AS special, date_start AS special_date_start, date_end AS special_date_end FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

        return $query->row;
    }

    public function getProductToFacebook($product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_facebook` WHERE product_id = '" . (int)$product_id . "'");

        if ($query->num_rows) {
            return array(
                'google_product_category'   => $query->row['google_product_category'],
                'condition'                 => strtolower($query->row['condition']),
                'age_group'                 => strtolower($query->row['age_group']),
                'color'                     => $query->row['color'],
                'gender'                    => strtolower($query->row['gender']),
                'material'                  => strtolower($query->row['material']),
                'pattern'                   => $query->row['pattern']
            );
        } else {
            return array(
                'google_product_category'   => '',
                'condition'                 => '',
                'age_group'                 => '',
                'color'                     => '',
                'gender'                    => '',
                'material'                  => '',
                'pattern'                   => ''
            );
        }
    }

    public function updateUseS2SUsePIIByAAMSetting() {
        $pixel_id = $this->config->get('facebook_pixel_id');
    
        if (empty($pixel_id)) {
            return;
        }

        // Fetch again after 20 minutes
        if (time() - $this->config->get('facebook_last_aam_check_time') < 60 * 20) {
            return;
        }
    
        $pixel_aam_settings = $this->getPixelAAMSettings($pixel_id);
        $pixel_enabled_aam_fields = $this->getPixelEnabledAAMFields($pixel_id);

        $data = array(
            'facebook_pixel_use_pii'            => $pixel_aam_settings,
            'facebook_pixel_enabled_aam_fields' => $pixel_enabled_aam_fields,
            'facebook_last_aam_check_time'      => time()
        );

        $this->updateFacebookSettings($data);
    }

    public function updateFacebookSettings($data = array()) {
        foreach ($data as $key => $value) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'facebook' AND `key` = '" . $this->db->escape($key) . "'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '0', `code` = 'facebook', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
        }
    }

    public function getAgentParameters() {
        $plugin_agent_name = 'exopencart';
        $opencart_version = VERSION;
        $plugin_version = $this->getPluginVersion();
    
        $agent_string = sprintf('%s-%s-%s', $plugin_agent_name, $opencart_version, $plugin_version);

        $facebook_pixel_params = array('agent' => $agent_string);

        return json_encode($facebook_pixel_params, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
    }

    public function getPii() {
        $facebook_pixel_pii = array();

        if ($this->config->get('facebook_pixel_use_pii')) {
            if ($this->customer->isLogged()) {
                $email = $this->customer->getEmail();
                $firstname = $this->customer->getFirstName();
                $lastname = $this->customer->getLastName();
                $telephone = $this->customer->getTelephone();
            } elseif (isset($this->session->data['guest'])) {
                $email = isset($this->session->data['guest']['email']) ? $this->session->data['guest']['email'] : '';
                $firstname = isset($this->session->data['guest']['firstname']) ? $this->session->data['guest']['firstname'] : '';
                $lastname = isset($this->session->data['guest']['lastname']) ? $this->session->data['guest']['lastname'] : '';
                $telephone = isset($this->session->data['guest']['telephone']) ? $this->session->data['guest']['telephone'] : '';
            } else {
                $email = '';
                $firstname = '';
                $lastname = '';
                $telephone = '';
            }

            $enabled_aam_fields = explode(',', $this->config->get('facebook_pixel_enabled_aam_fields'));

            if ($enabled_aam_fields) {
                if ($email && in_array('em', $enabled_aam_fields)) {
                    $facebook_pixel_pii['em'] = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');;
                }

                if ($firstname && in_array('fn', $enabled_aam_fields)) {
                    $facebook_pixel_pii['fn'] = htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8');
                }

                if ($lastname && in_array('ln', $enabled_aam_fields)) {
                    $facebook_pixel_pii['ln'] = htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8');
                }

                if ($telephone && in_array('ph', $enabled_aam_fields)) {
                    $facebook_pixel_pii['ph'] = htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8');
                }
            }
        }

        return json_encode($facebook_pixel_pii, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
    }

    public function getEventParameters() {
        $route = (array_key_exists('route', $this->request->get)) ? $this->request->get['route'] : null;
    
        $facebook_pixel_event_params = null;
    
        // This grabs events stored on redirects
        if (array_key_exists('facebook_pixel_event_params', $this->session->data)) {
            $facebook_pixel_event_params = $this->session->data['facebook_pixel_event_params'];
        }

        $event_name = 'ViewContent';
        $event_id = $this->generateEventId();
    
        // checking the route and handling the event firing accordingly
        switch ($route) {
            case 'checkout/success':
                $event_name = 'Purchase';

                $this->load->language('checkout/success');

                $contents = array();
                $content_ids = array();
                $value = 0;
                $num_items = 0;
                $currency = $this->session->data['currency'];

                if (isset($this->session->data['facebook_business_order_id'])) {
                    $order_id = $this->session->data['facebook_business_order_id'];
                    unset($this->session->data['facebook_business_order_id']);

                    $this->load->model('account/order');

                    $order_info = $this->model_account_order->getOrder($order_id);

                    if ($order_info) {
                        $order_products = $this->model_account_order->getOrderProducts($order_id);

                        foreach ($order_products as $product) {
                            $content_ids[] = (string)$product['product_id'];
                            $num_items += $product['quantity'];
                            $contents[] = array(
                                'id'       => $product['product_id'],
                                'quantity' => $product['quantity']
                            );
                        }

                        $value = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
                        $currency = $order_info['currency_code'];
                    }
                } 

                $facebook_pixel_event_params = array(
                    'event_name'    => $event_name,
                    'content_ids'   => $content_ids,
                    'content_name'  => $this->formatString($this->language->get('heading_title')),
                    'content_type'  => 'product',
                    'contents'      => $contents,
                    'currency'      => strtoupper($currency),
                    'num_items'     => $num_items,
                    'value'         => $value,
                    'event_id'      => $event_id
                );

                break;

            case 'product/product':
                $event_name = 'ViewContent';

                $this->load->model('catalog/product');

                $product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
                    if ((float)$product_info['special']) {
                        $price = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                    } else {
                        $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                    }

                    $facebook_pixel_event_params = array(
                        'event_name'       => $event_name,
                        'content_ids'      => array((string)$product_id),
                        'content_name'     => $this->formatString($product_info['name']),
                        'content_type'     => 'product',
                        'currency'         => strtoupper($this->session->data['currency']),
                        'value'            => $price,
                        'event_id'         => $event_id
                    );
                }

                break;

            case 'checkout/cart':
                $event_name = 'AddToCart';

                $this->load->language('checkout/cart');

                $contents = array();
                $content_ids = array();
                $value = 0.0;
                $num_items = 0;

                foreach ($this->cart->getProducts() as $product) {
                    $content_ids[] = (string)$product['product_id'];
                    $value += $this->currency->format($this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                    $num_items += $product['quantity'];
                    $contents[] = array(
                        'id'       => $product['product_id'],
                        'quantity' => $product['quantity']
                    );
                }

                $facebook_pixel_event_params = array(
                    'event_name'    => $event_name,
                    'content_ids'   => $content_ids,
                    'content_name'  => $this->formatString($this->language->get('heading_title')),
                    'content_type'  => 'product',
                    'contents'      => $contents,
                    'currency'      => strtoupper($this->session->data['currency']),
                    'value'         => $value,
                    'event_id'      => $event_id
                );

                break;

            case 'account/order/reorder':
                $event_name = 'AddToCart';

                if (isset($this->request->get['order_id'])) {
                    $order_id = $this->request->get['order_id'];
                } else {
                    $order_id = 0;
                }

                $this->load->model('account/order');

                $order_info = $this->model_account_order->getOrder($order_id);

                if ($order_info) {
                    if (isset($this->request->get['order_product_id'])) {
                        $order_product_id = $this->request->get['order_product_id'];
                    } else {
                        $order_product_id = 0;
                    }
              
                    $order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);

                    if ($order_product_info) {
                        $this->load->model('catalog/product');
                
                        $product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);
                
                        if ($product_info) {
                            if ((float)$product_info['special']) {
                                $price = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                            } else {
                                $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                            }

                            $price = $price * $order_product_info['quantity'];

                            $contents = array(
                                'id'       => $product_info['product_id'],
                                'quantity' => $order_product_info['quantity']
                            );

                            $facebook_pixel_event_params = array(
                                'event_name'    => $event_name,
                                'content_ids'   => array((string)$product_info['product_id']),
                                'content_name'  => $this->formatString($product_info['name']),
                                'content_type'  => 'product',
                                'contents'      => $contents,
                                'currency'      => strtoupper($this->session->data['currency']),
                                'value'         => $this->currency->format($this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false),
                                'event_id'      => $event_id
                            );
                        }
                    }
                }

                break;

            case 'checkout/checkout':
                $event_name = 'InitiateCheckout';

                $this->load->language('checkout/checkout');

                $contents = array();
                $content_ids = array();
                $value = 0;
                $num_items = 0;

                foreach ($this->cart->getProducts() as $product) {
                    $content_ids[] = (string)$product['product_id'];
                    $value += $this->currency->format($this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                    $num_items += $product['quantity'];
                    $contents[] = array(
                        'id'       => $product['product_id'],
                        'quantity' => $product['quantity']
                    );
                }

                $facebook_pixel_event_params = array(
                    'event_name'    => $event_name,
                    'content_name'  => $this->formatString($this->language->get('heading_title')),
                    'content_ids'   => $content_ids,
                    'content_type'  => 'product',
                    'contents'      => $contents,
                    'currency'      => strtoupper($this->session->data['currency']),
                    'num_items'     => $num_items,
                    'value'         => $value,
                    'event_id'      => $event_id
                );

                break;
    
            case 'product/search':
                $event_name = 'Search';

                $this->load->model('catalog/product');
                $this->load->language('product/search');

                if (isset($this->request->get['search'])) {
                    $search = $this->request->get['search'];
                } else {
                    $search = '';
                }

                if (isset($this->request->get['tag'])) {
                    $tag = $this->request->get['tag'];
                } elseif (isset($this->request->get['search'])) {
                    $tag = $this->request->get['search'];
                } else {
                    $tag = '';
                }

                if (isset($this->request->get['description'])) {
                    $description = $this->request->get['description'];
                } else {
                    $description = '';
                }
            
                if (isset($this->request->get['category_id'])) {
                    $category_id = $this->request->get['category_id'];
                } else {
                    $category_id = 0;
                }
            
                if (isset($this->request->get['sub_category'])) {
                    $sub_category = $this->request->get['sub_category'];
                } else {
                    $sub_category = '';
                }

                $page_filter_data = array(
                    'filter_name'         => $search,
                    'filter_tag'          => $tag,
                    'filter_description'  => $description,
                    'filter_category_id'  => $category_id,
                    'filter_sub_category' => $sub_category
                );

                $filter_data = $this->getFilterData($page_filter_data);

                $products = $this->model_catalog_product->getProducts($filter_data);

                $contents = array();
                $content_ids = array();
                $value = 0.0;
                $num_items = 0;

                foreach ($products as $product) {
                    $content_ids[] = $product['product_id'];

                    if ((float)$product['special']) {
                        $value += $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                    } else {
                        $value += $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                    }

                    $num_items++;
                    $contents[] = array(
                        'id'        => $product['product_id'],
                        'quantity'  => 1
                    );
                }

                if (isset($this->request->get['search'])) {
                    $search_string = $this->request->get['search'];
                } elseif (isset($this->request->get['tag'])) {
                    $search_string = $this->request->get['tag'];
                } else {
                    $search_string = '';
                }

                $facebook_pixel_event_params = array(
                    'event_name'    => $event_name,
                    'content_ids'   => $content_ids,
                    'content_name'  => $this->formatString($this->language->get('heading_title')),
                    'content_type'  => 'product',
                    'contents'      => $contents,
                    'currency'      => strtoupper($this->session->data['currency']),
                    'search_string' => $search_string,
                    'value'         => $value,
                    'num_items'     => $num_items,
                    'event_id'      => $event_id
                );

                break;

            case 'product/category':
                $event_name = 'ViewCategory';

                $this->load->model('catalog/category');

                if (isset($this->request->get['path'])) {
                    $category_id = $this->request->get['path'];
                } else {
                    $category_id = 0;
                }

                $category_info = $this->model_catalog_category->getCategory($category_id);

                if ($category_info) {
                    if (isset($this->request->get['filter'])) {
                        $filter = $this->request->get['filter'];
                    } else {
                        $filter = '';
                    }

                    $page_filter_data = array(
                        'filter_category_id'     => $category_id,
                        'filter_filter'          => $filter,
                    );

                    $filter_data = $this->getFilterData($page_filter_data);

                    $products = $this->model_catalog_product->getProducts($filter_data);

                    $contents = array();
                    $content_ids = array();
                    $value = 0.0;
                    $num_items = 0;
    
                    foreach ($products as $product) {
                        $content_ids[] = $product['product_id'];
    
                        if ((float)$product['special']) {
                            $value += $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                        } else {
                            $value += $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                        }
    
                        $num_items++;
                        $contents[] = array(
                            'id'       => $product['product_id'],
                            'quantity' => 1,
                        );
                    }

                    $facebook_pixel_event_params = array(
                        'event_name'        => $event_name,
                        'content_name'      => $this->formatString($category_info['name']),
                        'content_category'  => $this->formatString($category_info['name']),
                        'content_ids'       => $content_ids,
                        'content_type'      => 'product',
                        'contents'          => $contents,
                        'currency'          => strtoupper($this->session->data['currency']),
                        'value'             => $value,
                        'num_items'         => $num_items,
                        'event_id'          => $event_id
                    );
                } else {
                    $facebook_pixel_event_params = array('event_name' => $event_name);
                }
              
                break;

            case 'account/wishlist':
                $event_name = 'AddToWishlist';

                $this->load->language('account/wishlist');

                if (version_compare(VERSION, '2.0.3.1') <= 0) {
                    if (isset($this->session->data['wishlist'])) {
                        $wishlist = array_map(
                            function($product_id) {
                                return array('product_id' => $product_id);
                            },
                            $this->session->data['wishlist']
                        );
                    } else {
                        $wishlist = array();
                    }
                } else {
                    $this->load->model('account/wishlist');

                    $wishlist = $this->model_account_wishlist->getWishlist();
                }

                $this->load->model('catalog/product');

                $contents = array();
                $content_ids = array();
                $value = 0.0;
                $num_items = 0;

                foreach ($wishlist as $data) {
                    $product_info = $this->model_catalog_product->getProduct($data['product_id']);

                    if ($product_info) {
                        $content_ids[] = $product_info['product_id'];
                        
                        if ((float)$product_info['special']) {
                            $value += $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                        } else {
                            $value += $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                        }

                        $num_items++;

                        $contents[] = array(
                            'id'       => $product_info['product_id'],
                            'quantity' => 1
                        );
                    }
                }

                $facebook_pixel_event_params = array(
                    'event_name'    => $event_name,
                    'content_name'  => $this->formatString($this->language->get('heading_title')),
                    'content_ids'   => $content_ids,
                    'content_type'  => 'product',
                    'contents'      => $contents,
                    'currency'      => strtoupper($this->session->data['currency']),
                    'value'         => $value,
                    'num_items'     => $num_items,
                    'event_id'      => $event_id
                );

                break;

            case 'account/success':
                $event_name = 'CompleteRegistration';

                if ($this->customer->isLogged()) {
                    $this->language->load('account/success');

                    $facebook_pixel_event_params = array(
                        'event_name'   => $event_name,
                        'content_name' => $this->formatString($this->language->get('heading_title')),
                        'currency'     => strtoupper($this->session->data['currency']),
                        'status'       => true
                    );
                }

                break;
    
            case 'information/contact/success':
                $event_name = 'Contact';

                $facebook_pixel_event_params = array(
                    'event_name' => $event_name
                );

                break;
            
            case 'product/manufacturer/info':
                $event_name = 'ViewBrand';

                if (isset($this->request->get['manufacturer_id'])) {
                    $manufacturer_id = $this->request->get['manufacturer_id'];
                } else {
                    $manufacturer_id = 0;
                }

                if ($manufacturer_id) {
                    $this->load->model('catalog/manufacturer');

                    $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

                    if ($manufacturer_info) {
                        $this->load->model('catalog/product');

                        $page_filter_data = array(
                            'filter_manufacturer_id'     => $manufacturer_id
                        );
    
                        $filter_data = $this->getFilterData($page_filter_data);
                        
                        $products = $this->model_catalog_product->getProducts($filter_data);

                        $contents = array();
                        $content_ids = array();
                        $value = 0.0;
                        $num_items = 0;
        
                        foreach ($products as $product) {
                            $content_ids[] = $product['product_id'];
        
                            if ((float)$product['special']) {
                                $value += $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                            } else {
                                $value += $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                            }
        
                            $num_items++;
                            $contents[] = array(
                                'id'       => $product['product_id'],
                                'quantity' => 1,
                            );
                        }

                        $facebook_pixel_event_params = array(
                          'event_name'        => $event_name,
                          'content_name'      => $this->formatString($manufacturer_info['name']),
                          'content_category'  => $this->formatString($manufacturer_info['name']),
                          'content_ids'       => $content_ids,
                          'content_type'      => 'product',
                          'contents'          => $contents,
                          'currency'          => strtoupper($this->session->data['currency']),
                          'value'             => $value,
                          'num_items'         => $num_items,
                          'event_id'          => $event_id
                      );
                    } else {
                        $facebook_pixel_event_params = array('event_name' => $event_name);
                    }
                }

                break;
        }
    
        $this->trackPixel($facebook_pixel_event_params, $event_name, $event_id);

        if ($facebook_pixel_event_params) {
            return addslashes(json_encode($facebook_pixel_event_params));
        } else {
            return $facebook_pixel_event_params;
        }
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

    public function getPluginVersion() {
        return $this->pluginVersion;
    }

    public function formatString($string) {
        return trim(strip_tags(html_entity_decode(html_entity_decode($string), ENT_QUOTES | ENT_COMPAT, 'UTF-8')));
    }

    private function getFilterData($page_filter_data = array()) {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        } else {
            $limit = $this->config->get($this->config->get('config_theme') . '_product_limit');
        }

        $general_filter_data = array(
            'sort'                => $sort,
            'order'               => $order,
            'start'               => ($page - 1) * $limit,
            'limit'               => $limit
        );

        if ($page_filter_data) {
            $filter_data = array_merge($page_filter_data, $general_filter_data);
        } else {
            $filter_data = $general_filter_data;
        }

        return $filter_data;
    }

    public function trackPixel($server_event_params, $event_name, $event_id) {
        if (($this->config->get('facebook_business_cookie_bar_status') && (empty($_COOKIE['fb_cookieconsent_status']) || $_COOKIE['fb_cookieconsent_status'] !== 'deny'))
          || $server_event_params == null) {
            return;
        }

        if ($event_name == 'Purchase' && empty($server_event_params['content_ids'])) {
            return;
        }

        if ($this->config->get('facebook_use_s2s')) {
            $pixel_id = $this->config->get('facebook_pixel_id');
            $access_token = $this->config->get('facebook_system_user_access_token');
            $agent_data = json_decode($this->getAgentParameters(), true);
            $agent = $agent_data['agent'];
            $user_pii_data = json_decode($this->getPii(), true);

            try {
                $user_data = (new UserData())
                  ->setClientIpAddress(Util::getIpAddress())
                  ->setClientUserAgent(Util::getHttpUserAgent())
                  ->setFbp(Util::getFbp())
                  ->setFbc(Util::getFbc());
            
                $event = (new Event())
                  ->setEventName($event_name)
                  ->setEventTime(time())
                  ->setEventId($event_id)
                  ->setEventSourceUrl(Util::getRequestUri())
                  ->setActionSource(ActionSource::WEBSITE)
                  ->setUserData($user_data)
                  ->setDataProcessingOptions(array())
                  ->setDataProcessingOptionsCountry(0)
                  ->setDataProcessingOptionsState(0)
                  ->setCustomData(new CustomData());

                $enabled_aam_fields = explode(',', $this->config->get('facebook_pixel_enabled_aam_fields'));

                if ($enabled_aam_fields) {
                    if (!empty($user_pii_data['em']) && in_array('em', $enabled_aam_fields)) {
                        $user_data->setEmail($user_pii_data['em']);
                    }
            
                    if (!empty($user_pii_data['fn']) && in_array('fn', $enabled_aam_fields)) {
                        $user_data->setFirstName($user_pii_data['fn']);
                    }
            
                    if (!empty($user_pii_data['ln']) && in_array('ln', $enabled_aam_fields)) {
                        $user_data->setLastName($user_pii_data['ln']);
                    }
            
                    if (!empty($user_pii_data['ph']) && in_array('ph', $enabled_aam_fields)) {
                        $user_data->setPhone($user_pii_data['ph']);
                    }
                }

                $custom_data = $event->getCustomData();

                if (!empty($server_event_params['currency'])) {
                    $custom_data->setCurrency($server_event_params['currency']);
                }
          
                if (!empty($server_event_params['value'])) {
                    $custom_data->setValue($server_event_params['value']);
                }
          
                if (!empty($server_event_params['content_ids'])) {
                    $custom_data->setContentIds($server_event_params['content_ids']);
                }
          
                if (!empty($server_event_params['content_type'])) {
                    $custom_data->setContentType($server_event_params['content_type']);
                }
            } catch (Exception $ex) {
                $this->log->write('Facebook Business Extension :: Fail to create server event!');
                return false;
            }

            $api = Api::init(null, null, $access_token, false);

            $async_request = (new EventRequestAsync($pixel_id))
                  ->setEvents(array($event))
                  ->setPartnerAgent($agent);

            return $async_request->execute()
              ->then(
                null,
                function(\Exception $ex) {
                  $this->log->write('Facebook Business Extension :: Fail to send server event! Error Message: ' . $ex->getMessage());
                }
              );
        }
    }

    /**
     * Creates a new guid v4 - via https://stackoverflow.com/a/15875555
     * @return string A 36 character string containing dashes.
     */
    public function generateEventId() {
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
