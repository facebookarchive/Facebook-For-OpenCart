<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ControllerFacebookFacebookProduct extends Controller {
  // this is a backend controller for syncing facebook products,
  // so there is no frontend UI involved
  public function index() {
  }

  private function loadLibrariesForFacebookCatalog() {
    $this->load->model('catalog/product');
    $this->facebookcommonutils = new FacebookCommonUtils();
    $this->facebookgraphapi = new FacebookGraphAPI();
  }

  public function getProductInfoForFacebookPixel() {
    $this->loadLibrariesForFacebookCatalog();
    $json = array();
    $product_id = $this->request->get['product_id'];
    if ($product_id) {
      $product_info = $this->model_catalog_product->getProduct($product_id);
      if ($product_info) {
        $event_name = $this->request->get['event_name'];
        $product_info['quantity'] = isset($this->request->get['quantity'])
          ? $this->request->get['quantity']
          : 1;
        $params = new DAPixelConfigParams(array(
          'eventName' => $event_name,
          'products' => array($product_info),
          'currency' => $this->currency,
          'currencyCode' => $this->session->data['currency'],
          'hasQuantity' => true));
        $facebook_pixel_params =
          $this->facebookcommonutils->getDAPixelParamsForProducts($params);
        $json['facebook_pixel_event_params_FAE'] = $facebook_pixel_params;
      }
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function directCheckout() {
    // redirect is defaulted to merchant's home page.
    // This will be used if the we cant perform a direct checkout
    // due to invalid or missing product_id
    $url = $this->url->link('common/home', '', true);
    $product_id = $this->request->get['product_id'];
    if ($product_id) {
      $this->loadLibrariesForFacebookCatalog();
      $product_info = $this->model_catalog_product->getProduct($product_id);
      if ($product_info) {
        // attempts to add the product directly to cart
        $this->request->post['product_id'] = $product_id;
        $this->load->controller('checkout/cart/add');
        $result = json_decode($this->response->getOutput(), true);
        if (isset($result['success'])) {
          // successfully added product to cart, redirect to checkout
          $url = $this->url->link('checkout/checkout', '', true);
        } else {
          if (isset($result['redirect']) && $result['redirect']) {
            // unable to add to cart directly
            // 1 possibility is due to need to specify options for product
            // in this case, redirect based on the redirect url
            $url = $result['redirect'];
          }
        }
      }
    }
    $this->response->redirect($url);
  }
}
