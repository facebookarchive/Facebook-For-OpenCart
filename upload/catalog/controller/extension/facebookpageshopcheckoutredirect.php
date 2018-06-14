<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ControllerExtensionFacebookPageShopCheckoutRedirect extends Controller {
  // this is for redirecting checkout from page shop
  // of products still having the old checkout URL
  // to the product URL
  public function index() {
    $target = 'facebook/facebookproduct/directcheckout';
    if (isset($this->request->get['route'])
      && substr((string)$this->request->get['route'], 0, strlen($target)) == $target) {
      $url = $this->url->link('common/home', '', true);
      if (isset($this->request->get['product_id'])) {
        $args = 'product_id='.$this->request->get['product_id'];
        $url = $this->url->link('product/product', $args, true);
      }
      $this->response->redirect($url);
    }
  }

}
