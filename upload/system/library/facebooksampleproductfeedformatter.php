<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class FacebookSampleProductFeedFormatter extends FacebookProductFeedFormatter {
  protected function postFormatting($product_data) {
    // adds in a sales period which concats the start and end date
    // and provides the time period
    $product_data['sale_price_period'] =
      $product_data['sale_price_start_date'] .
      '/' .
      $product_data['sale_price_end_date'];

    array_walk($product_data, function(&$value, $key) {
      $value = (is_string($value))
        ? $value
        : (string)$value;
    });

    // duplicates the image_url as image_link and url as link
    $product_data['image_link'] = $product_data['image_url'];
    $product_data['link'] = $product_data['url'];

    return $product_data;
  }
}
