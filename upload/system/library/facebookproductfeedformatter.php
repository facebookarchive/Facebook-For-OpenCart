<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class FacebookProductFeedFormatter extends FacebookProductFormatter {
  public function getFormattedPrice($value) {
    // returns the price as dollars and cents
    $decimal_place = ($this->params->hasCents()) ? 2 : 0;
    return (string)(round(floatval($value), $decimal_place)) .
      ' ' .
      $this->params->getCurrencyCode();
  }

  protected function formatAdditionalImageUrls($product_image_urls) {
    // returns the additional image urls as a string
   if ($product_image_urls) {
      return implode(',', $product_image_urls);
    } else {
      return '';
    }
  }

  protected function postFormatting($product_data) {
    // adds in a sales period which concats the start and end date
    // and provides the time period
    $product_data['sale_price_period'] =
      $product_data['sale_price_start_date'] .
      '/' .
      $product_data['sale_price_end_date'];

    // escapes " in all string values to ""
    // and adds double quotes to contain the value
    // converts all non strings to string values
    array_walk($product_data, function(&$value, $key) {
      $value = (is_string($value))
        ? '"' . str_replace(array("\""), '""', $value) . '"'
        : (string)$value;
    });

    // duplicates the image_url as image_link and url as link
    $product_data['image_link'] = $product_data['image_url'];
    $product_data['link'] = $product_data['url'];

    return $product_data;
  }

  protected function escapeImageUrl($image_url) {
    // no escape needed for feed format
    return $image_url;
  }
}
