<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

/*
 * Builds up the event data for the request in a single place
 */
class ControllerExtensionFacebookEvents extends Controller {
  public function index() {
    $data = array();
    $this->fbutils = new FacebookCommonUtils();

    // we are storing all the pixel data in the fbevents parameters
    // this fbevents will be sent over to the header.php
    // which will then be sent to the header.tpl as $data to be
    // fired as FB pixel events via javascript
    $data['facebook_pixel_id_FAE'] = $this->config->get('facebook_pixel_id');
    $data['facebook_pixel_params_FAE'] = $this->getAgentParameters();
    $data['facebook_pixel_pii_FAE'] = $this->getPii();
    $data['facebook_pixel_event_params_FAE'] = $this->getEventParameters();

    $this->fbevents = $data;

    // note that the $this->session->data['facebook_pixel_event_params_FAE']
    // is unset in the header.php instead of this location as there may be
    // redirects of the webpages
  }

  private function getAgentParameters() {
    $source = 'exopencart';
    $opencart_version = VERSION;
    $plugin_version = $this->fbutils->getPluginVersion();
    $agent_string = sprintf(
      '%s-%s-%s',
      $source,
      $opencart_version,
      $plugin_version);
    $facebook_pixel_params_fae = array('agent' => $agent_string);
    return json_encode(
      $facebook_pixel_params_fae,
      JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
  }

  private function getPii() {
    $facebook_pixel_pii_fae = array();
    if ($this->config->get('facebook_pixel_use_pii') === 'true'
      && $this->customer->isLogged()) {
      $facebook_pixel_pii_fae['em'] =
        $this->fbutils->getEscapedString(
          $this->customer->getEmail());
      $facebook_pixel_pii_fae['fn'] =
        $this->fbutils->getEscapedString(
          $this->customer->getFirstName());
      $facebook_pixel_pii_fae['ln'] =
        $this->fbutils->getEscapedString(
          $this->customer->getLastName());
      $facebook_pixel_pii_fae['ph'] =
        $this->fbutils->getEscapedString(
          $this->customer->getTelephone());
    }
    return json_encode(
      $facebook_pixel_pii_fae,
      JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
  }

  private function getEventParameters() {
    $route = (array_key_exists('route', $this->request->get))
      ? $this->request->get['route']
      : null;

    $facebook_pixel_event_params_fae = null;

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
        $facebook_pixel_event_params_fae = $this->getAddToCartEventParameters(
          $products);
        break;
      }

      case 'account/order/info': {
        $product_id = (isset($this->session->data['product_id']))
          ? (int)$this->session->data['product_id']
          : 0;
        $quantity = (isset($this->session->data['quantity']))
          ? $this->session->data['quantity']
          : 1;
        $product_info = $this->getProductDetails($product_id, $quantity);
        if ($product_info) {
          $facebook_pixel_event_params_fae =
            $this->getAddToCartEventParameters(
              array($product_info));
        }
        break;
      }

      case 'checkout/checkout': {
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

      case 'account/newsletter': {
        $facebook_pixel_event_params_fae =
          $this->getSubscribeEventParameters();
        // storing this into the session as the newsletter subscription is
        // redirected back to the account/account page
        $this->session->data['facebook_pixel_event_params_FAE'] =
          $facebook_pixel_event_params_fae;
        break;
      }
    }

    return ($facebook_pixel_event_params_fae)
      ? addslashes(json_encode($facebook_pixel_event_params_fae))
      : $facebook_pixel_event_params_fae;
  }

  private function getProductDetails($product_id, $quantity) {
    $this->load->model('catalog/product');
    $product_info = $this->model_catalog_product->getProduct($product_id);
    if ($product_info) {
      $product_info['quantity'] = $quantity;
    }
    return $product_info;
  }

  private function getPurchaseEventParameters() {
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

  private function getAddToCartEventParameters(
    $products) {
    return $this->generateEventParameters(
      $products,
      'AddToCart',
      true);
  }

  private function getInitiateCheckoutEventParameters() {
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
      ? $this->request->get['page']
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

    $tag = (isset($this->request->get['tag']))
      ? $this->request->get['tag']
      : (isset($this->request->get['search']))
        ? $this->request->get['search']
        : '';

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
    if (!isset($this->request->get['search'])
      && isset($this->request->get['tag'])) {
      return null;
    }

    $filter_data = $this->getSearchFilterParameters();
    $this->load->model('catalog/product');
    $products = $this->model_catalog_product->getProducts($filter_data);

    $params = new DAPixelConfigParams(array(
      'eventName' => 'Search',
      'products' => $products,
      'currency' => $this->currency,
      'currencyCode' => $this->session->data['currency'],
      'hasQuantity' => false,
      'isCustomEvent' => false,
      'paramNameUsedInProductListing' => 'search_string',
      'paramValueUsedInProductListing' => $filter_data['filter_name']));
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
      : array();
  }

  private function getViewCategoryEventParameters() {
    $filter_data = $this->getSearchCategoryFilterParameters();
    $this->load->model('catalog/category');
    $category_info = $this->model_catalog_category->getCategory(
      $filter_data['filter_category_id']);

    if (!$category_info) {
      return null;
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
      : array();
  }

  private function getViewBrandEventParameters() {
    $filter_data = $this->getSearchBrandFilterParameters();
    $this->load->model('catalog/manufacturer');
    $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(
      $filter_data['filter_manufacturer_id']);

    if (!$manufacturer_info) {
      return null;
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

  private function getSubscribeEventParameters() {
    return ($this->request->server['REQUEST_METHOD'] == 'POST'
      && isset($this->request->post['newsletter']))
      ? array(
        'event_name' => 'Subscribe',
        'status' => ($this->request->post['newsletter'])
          ? 'Subscribe newsletter'
          : 'Unsubscribe newsletter')
      : null;
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
    return $this->fbutils->getDAPixelParamsForProducts($params);
  }
}
