<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

/*
 * Builds up the event data for the request in a single place
 */
require_once DIR_APPLICATION . '../system/library/vendor/autoload.php';
require_once DIR_APPLICATION . '../system/library/servereventfactory.php';
require_once DIR_APPLICATION . '../system/library/facebookserversideevent.php';

use FacebookPixelPlugin\Core\ServerEventFactory;
use FacebookPixelPlugin\Core\FacebookServerSideEvent;

class ControllerExtensionFacebookEventParameters extends Controller {
  private $event_id = null;
  private $user_pii_data = null;

  public function __construct($registry) {
    parent::__construct($registry);
    if (!$this->areRequiredFilesPresent()) {
      return;
    }
    $this->fbutils = new FacebookCommonUtils();
  }

  public function index() {
    // if the required files are not present, we should return
    if (!$this->areRequiredFilesPresent()) {
      return;
    }

    $fbevents = $this->fbevents;
    if (isset($fbevents)) {
      $facebook_pixel_event_params_FAE =
        $this->fbevents['facebook_pixel_event_params_FAE'];
      if (isset($facebook_pixel_event_params_FAE)) {
        // detects that the Facebook event params are already set
        // we can safely exit as there is no further work needed
        return;
      }
    }

    try {
      $data = array();

      // always fetch the latest pixel AAM setting from Events Manager
      $this->updateUseS2SUsePIIByAAMSetting();

      // we are storing all the pixel data in the fbevents parameters
      // this fbevents will be sent over to the header.php
      // which will then be sent to the header.tpl as $data to be
      // fired as FB pixel events via javascript
      $data['facebook_pixel_id_FAE'] = $this->config->get('facebook_pixel_id');
      $data['facebook_pixel_params_FAE'] = $this->getAgentParameters();
      $this->user_pii_data = $this->fbutils->getPii(
        $this->config,
        $this->customer,
        $this->fbutils->getGuestLogin($this->session));
      $data['facebook_pixel_pii_FAE'] = json_encode(
        $this->user_pii_data,
        JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
      $data['facebook_pixel_event_params_FAE'] = $this->getEventParameters();
      $data['facebook_enable_cookie_bar'] =
        ($this->config->get(FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR))
        ? $this->config->get(FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR)
        : 'true';

      $this->fbevents = $data;
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }

  private function getRequiredFiles() {
    return array(
// system auto generated, DO NOT MODIFY
      DIR_APPLICATION . '/../system/library/controller/extension/facebookproductfeed.php',
      DIR_APPLICATION . '/../system/library/eventidgenerator.php',
      DIR_APPLICATION . '/../system/library/facebookcommonutils.php',
      DIR_APPLICATION . '/../system/library/facebookgraphapi.php',
      DIR_APPLICATION . '/../system/library/facebookgraphapierror.php',
      DIR_APPLICATION . '/../system/library/facebookproductfeedformatter.php',
      DIR_APPLICATION . '/../system/library/facebookproductformatter.php',
      DIR_APPLICATION . '/../system/library/facebookproducttrait.php',
      DIR_APPLICATION . '/../system/library/facebooksampleproductfeedformatter.php',
      DIR_APPLICATION . '/../system/library/facebookserversideevent.php',
      DIR_APPLICATION . '/../system/library/facebooktax.php',
      DIR_APPLICATION . '/../system/library/model/extension/facebookproduct.php',
      DIR_APPLICATION . '/../system/library/model/extension/facebooksetting.php',
      DIR_APPLICATION . '/../system/library/servereventfactory.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookeventparameters.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookfeed.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookpageshopcheckoutredirect.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookproduct.php',
      DIR_APPLICATION . '/../catalog/view/javascript/facebook/cookieconsent.min.js',
      DIR_APPLICATION . '/../catalog/view/javascript/facebook/facebook_pixel_3_1_1.js',
      DIR_APPLICATION . '/../catalog/view/theme/css/facebook/cookieconsent.min.css',
// system auto generated, DO NOT MODIFY
      '');
  }

  private function areRequiredFilesPresent() {
    foreach ($this->getRequiredFiles() as $filename) {
      if ($filename && !is_file($filename)) {
        return false;
      }
    }
    return true;
  }

  private function updateUseS2SUsePIIByAAMSetting() {
    $pixel_id = $this->config->get('facebook_pixel_id');

    if (empty($pixel_id)) {
      return;
    }

    $this->model_extension_facebooksetting = $this->fbutils
      ->loadFacebookSettingsModel($this->registry);
    $facebook_setting = $this->model_extension_facebooksetting->getSettings();
    $last_aam_check_time = $facebook_setting[FacebookCommonUtils::FACEBOOK_LAST_AAM_CHECK_TIME];

    // fetch again after 20mins
    if (time() - $last_aam_check_time < 60*20) {
      return;
    }

    $pixel_aam_setting = $this->fbutils->getPixelAAMSetting($pixel_id);
    $pixel_enabled_amm_fields = $this->fbutils->getPixelEnabledAAMFields($pixel_id);
    $data = array(
      FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII => $pixel_aam_setting,
      FacebookCommonUtils::FACEBOOK_PIXEL_ENABLED_AAM_FIELDS => $pixel_enabled_amm_fields,
      FacebookCommonUtils::FACEBOOK_LAST_AAM_CHECK_TIME => time()
    );
    $this->model_extension_facebooksetting->updateSettings($data);
  }

  private function getAgentParameters() {
    $agent_string = $this->fbutils->getAgentString();
    $facebook_pixel_params_fae = array('agent' => $agent_string);
    return json_encode(
      $facebook_pixel_params_fae,
      JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
  }

  private function getEventParameters() {
    $route = (array_key_exists('route', $this->request->get))
      ? $this->request->get['route']
      : null;

    $facebook_pixel_event_params_fae = null;
    $server_event = null;

    // This grabs events stored on redirects
    if (array_key_exists(
      'facebook_pixel_event_params_FAE',
      $this->session->data)) {
      $facebook_pixel_event_params_fae =
        $this->session->data['facebook_pixel_event_params_FAE'];
    }

    // checking the route and handling the event firing accordingly
    switch ($route) {
      case 'checkout/success': {
        $server_event = ServerEventFactory::safeCreateEvent(
          'Purchase',
          array($this, 'getPurchaseEventParameters'),
          array(),
          $this->user_pii_data,
          $this->config
        );
        $this->event_id = empty($server_event) ? null : $server_event->getEventId();
        $facebook_pixel_event_params_fae = $this->getPurchaseEventParameters();
        break;
      }

      case 'product/product': {
        $facebook_pixel_event_params_fae =
          $this->getViewContentEventParameters();
        break;
      }

      case 'checkout/cart': {
        $products = $this->cart->getProducts();
        $server_event = ServerEventFactory::safeCreateEvent(
          'AddToCart',
          array($this, 'getAddToCartEventParameters'),
          array($products),
          $this->user_pii_data,
          $this->config
        );
        $this->event_id = empty($server_event) ? null : $server_event->getEventId();
        $facebook_pixel_event_params_fae = $this->getAddToCartEventParameters(
          $products);
        break;
      }

      case 'account/order/reorder': {
        $order_product_info = $this->getOrderProductInfo();
        if ($order_product_info) {
          $product_info = $this->getProductDetails($order_product_info['product_id'], $order_product_info['quantity']);
          if ($product_info) {
            $server_event = ServerEventFactory::safeCreateEvent(
              'AddToCart',
              array($this, 'getAddToCartEventParameters'),
              array(array($product_info)),
              $this->user_pii_data,
              $this->config
            );
            $this->event_id = empty($server_event) ? null : $server_event->getEventId();
            $facebook_pixel_event_params_fae =
              $this->getAddToCartEventParameters(
                array($product_info));
          }
        }
        break;
      }

      case 'checkout/checkout': {
        $server_event = ServerEventFactory::safeCreateEvent(
          'InitiateCheckout',
          array($this, 'getInitiateCheckoutEventParameters'),
          array(),
          $this->user_pii_data,
          $this->config
        );
        $this->event_id = empty($server_event) ? null : $server_event->getEventId();
        $facebook_pixel_event_params_fae =
          $this->getInitiateCheckoutEventParameters();
        break;
      }

      case 'product/search': {
        $facebook_pixel_event_params_fae =
          $this->getSearchEventParameters();
        break;
      }

      case 'product/category': {
        $facebook_pixel_event_params_fae =
          $this->getViewCategoryEventParameters();
        break;
      }

      case 'account/wishlist': {
        $facebook_pixel_event_params_fae =
          $this->getWishlistEventParameters();
        break;
      }

      case 'account/success': {
        $facebook_pixel_event_params_fae =
          $this->getCompleteRegistrationEventParameters();
        break;
      }

      case 'information/contact/success': {
        $facebook_pixel_event_params_fae = $this->getContactEventParameters();
        break;
      }

      case 'product/manufacturer/info': {
        $facebook_pixel_event_params_fae =
          $this->getViewBrandEventParameters();
        break;
      }
    }

    FacebookServerSideEvent::getInstance()->track($server_event, $this->config);

    return ($facebook_pixel_event_params_fae)
      ? addslashes(json_encode($facebook_pixel_event_params_fae))
      : $facebook_pixel_event_params_fae;
  }

  private function getOrderProductInfo() {
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
    }

    $order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);
    return $order_product_info;
  }

  private function getProductDetails($product_id, $quantity) {
    $this->load->model('catalog/product');
    $product_info = $this->model_catalog_product->getProduct($product_id);
    if ($product_info) {
      $product_info['quantity'] = $quantity;
    }
    return $product_info;
  }

  public function getPurchaseEventParameters() {
    $products = $this->cart->getProducts();
    return $this->generateEventParameters(
      $products,
      'Purchase',
      true);
  }

  private function getViewContentEventParameters() {
    $product_id = (isset($this->request->get['product_id']))
      ? (int)$this->request->get['product_id']
      : 0;
    $product_info = $this->getProductDetails($product_id, 1);
    return ($product_info)
      ? $this->generateEventParameters(
        array($product_info),
        'ViewContent',
        false)
      : array();
  }

  public function getAddToCartEventParameters(
    $products) {
    return $this->generateEventParameters(
      $products,
      'AddToCart',
      true);
  }

  public function getInitiateCheckoutEventParameters() {
    $products = $this->cart->getProducts();
    return $this->generateEventParameters(
      $products,
      'InitiateCheckout',
      true);
  }

  private function getSortParameter() {
    return (isset($this->request->get['sort']))
      ? $this->request->get['sort']
      : 'p.sort_order';
  }

  private function getOrderParameter() {
    return (isset($this->request->get['order']))
      ? $this->request->get['order']
      : 'ASC';
  }

  private function getPageParameter() {
    return (isset($this->request->get['page']))
      ? (int)$this->request->get['page']
      : 1;
  }

  private function getLimitParameter() {
    // return limit if specified as request parameter
    if (isset($this->request->get['limit'])) {
      return (int)$this->request->get['limit'];
    }

    // OpenCart v2.0.1.1 to v2.1.0.2
    $limit = (int)$this->config->get('config_product_limit');
    if ($limit) {
      return $limit;
    }

    // OpenCart v2.2.0.0 to v2.3.0.2
    $limit = (int)$this->config->get(
      $this->config->get('config_theme') . '_product_limit');
    if ($limit) {
      return $limit;
    }

    // OpenCart v3.0.0.0 to v3.0.2.0b
    $limit = (int)$this->config->get(
      'theme_' . $this->config->get('config_theme') . '_product_limit');
    if ($limit) {
      return $limit;
    }

    return 15;
  }

  private function getGeneralFilterParameters() {
    return array(
      $this->getSortParameter(),
      $this->getOrderParameter(),
      $this->getPageParameter(),
      $this->getLimitParameter());
  }

  private function getSearchFilterParameters() {
    // replicating the filter param extraction from OpenCart core codes
    // https://github.com/opencart/opencart/blob/master/upload/catalog/controller/product/search.php#L12-L66
    $search = (isset($this->request->get['search']))
      ? $this->request->get['search']
      : '';

    // ternary operation does not work in here
    // reverted to regular nested if-else conditions
    $tag = '';
    if (isset($this->request->get['tag'])) {
      $tag = $this->request->get['tag'];
    } else {
      $tag = (isset($this->request->get['search']))
        ? $this->request->get['search']
        : '';
    }

    $description = (isset($this->request->get['description']))
      ? $this->request->get['description']
      : '';

    $category_id =  (isset($this->request->get['category_id']))
      ? $this->request->get['category_id']
      : 0;

    $sub_category = (isset($this->request->get['sub_category']))
      ? $this->request->get['sub_category']
      : '';

    list($sort, $order, $page, $limit) = $this->getGeneralFilterParameters();

    return
      (isset($this->request->get['search']) || isset($this->request->get['tag']))
        ? array(
          'filter_name'         => $search,
          'filter_tag'          => $tag,
          'filter_description'  => $description,
          'filter_category_id'  => $category_id,
          'filter_sub_category' => $sub_category,
          'sort'                => $sort,
          'order'               => $order,
          'start'               => ($page - 1) * $limit,
          'limit'               => $limit)
        : array();
  }

  private function getSearchEventParameters() {
    if (isset($this->request->get['search'])
      || isset($this->request->get['tag'])) {
      $filter_data = $this->getSearchFilterParameters();
      $this->load->model('catalog/product');
      $products = $this->model_catalog_product->getProducts($filter_data);
    } else {
      $products = array();
    }

    $params = new DAPixelConfigParams(array(
      'eventName' => 'Search',
      'products' => $products,
      'currency' => $this->currency,
      'currencyCode' => $this->session->data['currency'],
      'hasQuantity' => false,
      'isCustomEvent' => false,
      'paramNameUsedInProductListing' => 'search_string',
      'paramValueUsedInProductListing' => (isset($filter_data['filter_name']))
        ? $filter_data['filter_name']
        : ''));
    return $this->fbutils->getDAPixelParamsForProductListing($params);
  }

  private function getSearchCategoryFilterParameters() {
    // replicating the filter category param extraction from OpenCart core codes
    // https://github.com/opencart/opencart/blob/master/upload/catalog/controller/product/category.php
    if (isset($this->request->get['path'])) {
      $parts = explode('_', (string)$this->request->get['path']);
      $category_id = (int)array_pop($parts);
    } else {
      $category_id = 0;
    }

    $filter = (isset($this->request->get['filter']))
      ? $this->request->get['filter']
      : $filter = '';

    list($sort, $order, $page, $limit) = $this->getGeneralFilterParameters();

    return (isset($this->request->get['path']))
      ? array(
        'filter_category_id' => $category_id,
        'filter_filter'      => $filter,
        'sort'               => $sort,
        'order'              => $order,
        'start'              => ($page - 1) * $limit,
        'limit'              => $limit)
      // use a non-existing PK of -1 to indicate invalid category id
      : array('filter_category_id' => -1);
  }

  private function getViewCategoryEventParameters() {
    $filter_data = $this->getSearchCategoryFilterParameters();
    $this->load->model('catalog/category');
    $category_info = $this->model_catalog_category->getCategory(
      $filter_data['filter_category_id']);

    if (!$category_info) {
      // unable to find the category, just return ViewCategory event
      return array('event_name' => 'ViewCategory');
    }

    $this->load->model('catalog/product');
    $products = $this->model_catalog_product->getProducts($filter_data);

    $params = new DAPixelConfigParams(array(
      'eventName' => 'ViewCategory',
      'products' => $products,
      'currency' => $this->currency,
      'currencyCode' => $this->session->data['currency'],
      'hasQuantity' => false,
      'isCustomEvent' => false,
      'paramNameUsedInProductListing' => 'content_category',
      'paramValueUsedInProductListing' => $category_info['name']));
    return $this->fbutils->getDAPixelParamsForProductListing($params);
  }

  private function getWishlistEventParameters() {
    // the mechanism of getting the wishlist varies
    // between OpenCart v2.0.3.1 and higher
    if (version_compare(VERSION , '2.0.3.1') <= 0) {
      $wishlist = (isset($this->session->data['wishlist']))
        ? array_map(
          function($product_id) {
            return array('product_id' => $product_id);
          },
          $this->session->data['wishlist'])
        : array();
    } else {
      $this->load->model('account/wishlist');
      $wishlist = $this->model_account_wishlist->getWishlist();
    }

    $this->load->model('catalog/product');
    $products = array();
    foreach ($wishlist as $data) {
      $product_info = $this->model_catalog_product->getProduct($data['product_id']);
      if ($product_info) {
        $products[] = $product_info;
      }
    }
    $params = new DAPixelConfigParams(array(
      'eventName' => 'AddToWishlist',
      'products' => $products,
      'currency' => $this->currency,
      'currencyCode' => $this->session->data['currency'],
      'hasQuantity' => false));
    return $this->fbutils->getDAPixelParamsForProducts($params);
  }

  private function getCompleteRegistrationEventParameters() {
    return ($this->customer->isLogged())
      ? array(
        'event_name' => 'CompleteRegistration',
        'status' => 'Successful')
      : null;
  }

  private function getContactEventParameters() {
    return array('event_name' => 'Contact');
  }

  private function getSearchBrandFilterParameters() {
    // replicating the filter manufacturer param extraction from OpenCart core codes
    // https://github.com/opencart/opencart/blob/master/upload/catalog/controller/product/manufacturer.php
    $manufacturer_id = (isset($this->request->get['manufacturer_id']))
      ? (int)$this->request->get['manufacturer_id']
      : 0;

    list($sort, $order, $page, $limit) = $this->getGeneralFilterParameters();

    return (isset($this->request->get['manufacturer_id']))
      ? array(
        'filter_manufacturer_id' => $manufacturer_id,
        'sort'                   => $sort,
        'order'                  => $order,
        'start'                  => ($page - 1) * $limit,
        'limit'                  => $limit)
      // use a non-existing PK of -1 to indicate invalid manufacturer id
      : array('filter_manufacturer_id' => -1);
  }

  private function getViewBrandEventParameters() {
    $filter_data = $this->getSearchBrandFilterParameters();
    $this->load->model('catalog/manufacturer');
    $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(
      $filter_data['filter_manufacturer_id']);

    if (!$manufacturer_info) {
      // unable to find the manufacturer, just return ViewBrand event
      return array('event_name' => 'ViewBrand');
    }

    $this->load->model('catalog/product');
    $products = $this->model_catalog_product->getProducts($filter_data);

    $params = new DAPixelConfigParams(array(
      'eventName' => 'ViewBrand',
      'products' => $products,
      'currency' => $this->currency,
      'currencyCode' => $this->session->data['currency'],
      'hasQuantity' => false,
      'isCustomEvent' => true,
      'paramNameUsedInProductListing' => 'content_brand',
      'paramValueUsedInProductListing' => $manufacturer_info['name']));
    return $this->fbutils->getDAPixelParamsForProductListing($params);
  }

  private function generateEventParameters(
    $products,
    $event_name,
    $has_quantity) {
    if (!sizeof($products)) {
      return null;
    }

    $params = new DAPixelConfigParams(array(
      'eventName' => $event_name,
      'products' => $products,
      'currency' => $this->currency,
      'currencyCode' => $this->session->data['currency'],
      'hasQuantity' => $has_quantity));
    return $this->fbutils->getDAPixelParamsForProducts($params, $this->event_id);

  }
}
