<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class FacebookProductAPIFormatter extends FacebookProductFormatter {
  public function getFormattedPrice($value) {
    // returns the price as cents
    $decimal_place = ($this->params->hasCents()) ? 2 : 0;
    return intval(round(floatval($value), $decimal_place) * 100);
  }

  protected function formatAdditionalImageUrls($product_image_urls) {
    // returns the additional image urls as a list in string format
    // we need to escape the single quote for the image_url
   if ($product_image_urls) {
      array_walk($product_image_urls, function(&$value, $key) {
        $value = addslashes($value);
      });
      return "['" . implode("','", $product_image_urls) . "']";
    } else {
      return "[]";
    }
  }

  protected function postFormatting($product_data) {
    // no further post formatting
    return $product_data;
  }
}
