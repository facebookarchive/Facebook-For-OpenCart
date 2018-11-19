<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ControllerExtensionFacebookProduct extends Controller {
  public function __construct($registry) {
    parent::__construct($registry);
  }


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
}
