<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class FacebookProductFormatterParams {
  private $configTax; // 1 = show product price with tax, 0 = otherwise
  private $currencyCode; // default currency code of the store
  private $tax; // object to calculate the tax of products
  private $hasCents; // 1 = currency supports cents, 0 = otherwise
  private $modelCatalogProduct; // model object to retrieve product discounts
  private $storeName; // name of the store, used if brand or category is empty
  private $enableSpecialPrice; // decide if we should use the special price as discount

  public function __construct(
    $params) {
    if (isset($params['configTax'])) {
      $this->configTax = $params['configTax'];
    }
    if (isset($params['currencyCode'])) {
      $this->currencyCode = $params['currencyCode'];
    }
    if (isset($params['hasCents'])) {
      $this->hasCents = $params['hasCents'];
    }
    if (isset($params['modelCatalogProduct'])) {
      $this->modelCatalogProduct = $params['modelCatalogProduct'];
    }
    if (isset($params['storeName'])) {
      $this->storeName = $params['storeName'];
    }
    if (isset($params['tax'])) {
      $this->tax = $params['tax'];
    }
    if (isset($params['enableSpecialPrice'])) {
      $this->enableSpecialPrice = $params['enableSpecialPrice'];
    }
  }

  public function getConfigTax() {
    return $this->configTax;
  }

  public function getCurrencyCode() {
    return strtoupper($this->currencyCode);
  }

  public function hasCents() {
    return $this->hasCents;
  }

  public function getModelCatalogProduct() {
    return $this->modelCatalogProduct;
  }

  public function getStoreName() {
    return $this->storeName;
  }

  public function getTax() {
    return $this->tax;
  }

  public function hasEnabledSpecialPrice() {
    return $this->enableSpecialPrice;
  }
}

abstract class FacebookProductFormatter {
  const EMPTY_DATE = '0000-00-00';
  const MAX_DATE = '2038-01-17';
  const MIN_DATE = '1970-01-30';
  const MAX_TIME = 'T23:59+00:00';
  const MIN_TIME = 'T00:00+00:00';

  protected $facebookcommonutils;
  protected $params;

  public function setup(
    $params) {
    $this->params = $params;
    $this->facebookcommonutils = new FacebookCommonUtils();
  }

  public function getProductData(
    $registry,
    $product) {
    $product_data = array(
      'retailer_id' => $this->getRetailerID($product),
      'name' => $this->getName($product),
      'description' => $this->getDescription($product),
      'image_url' => $this->getImageUrl($product),
      'url' => $this->getUrl($product),
      'category' => $this->getCategory($product),
      'brand' => $this->getBrand($product),
      'price' => $this->getPrice($product),
      'currency' => $this->params->getCurrencyCode(),
      'availability' => $this->getAvailability($product),
      'retailer_product_group_id' =>
        $this->getRetailerProductGroupID($product),
      'checkout_url' => $this->getCheckoutUrl($product),
      'additional_image_urls' => $this->getAdditionalImageUrls($product),
      'condition' => 'new');
    $product_discount_data = $this->getDiscountPrice($registry, $product);
    $product_data = array_merge($product_data, $product_discount_data);
    return $this->postFormatting($product_data);
  }

  private function getRetailerID($product) {
    return $product['product_id'];
  }

  private function getName($product) {
    $name = $this->facebookcommonutils->getProperFormattedString(
      $product['name']);
    return $this->facebookcommonutils->trimText($name, 100);
  }

  private function getDescription($product) {
    $description = $this->facebookcommonutils->getProperFormattedString(
      $product['description']);
    if (!$description) {
      // fallback to meta_description if description is not available
      $description = $this->facebookcommonutils->getProperFormattedString(
        $product['meta_description']);
    }
    if (!$description) {
      // fallback to name if both descriptions are not available
      $description = $this->facebookcommonutils->getProperFormattedString(
        $product['name']);
    }
    $description =
      $this->facebookcommonutils->lowercaseIfAllCaps($description);

    return $this->facebookcommonutils->trimText($description, 5000);
  }

  private function getCategory($product) {
    $category = $this->facebookcommonutils->getProperFormattedString(
      $product['category_name']);
    if (!$category) {
      $category = $this->params->getStoreName();
    }
    return $this->facebookcommonutils->trimText($category, 250);
  }

  private function getBrand($product) {
    $brand = $this->facebookcommonutils->getProperFormattedString(
      $product['manufacturer_name']);
    if (!$brand) {
      $brand = $this->params->getStoreName();
    }
    return $this->facebookcommonutils->trimText($brand, 70);
  }

  private function getImageUrl($product) {
    return $this->formatImageUrl($product['image']);
  }

  private function getStoreBaseUrl() {
    if(defined('HTTP_CATALOG')) {
      return HTTP_CATALOG;
    }
    return HTTP_SERVER;
  }

  private function getUrl($product) {
    return $this->getStoreBaseUrl() .
      'index.php?route=product/product&product_id=' .
      $product['product_id'];
  }

  private function getPrice($product) {
    $price = $this->getPriceAfterTax(
      $product['price'],
      $product['tax_class_id']);
    return $this->getFormattedPrice($price);
  }

  public function getAvailability($product) {
    // we are re-writing the stock availability status
    // to follow that of OpenCart logic
    // https://github.com/opencart/opencart/blob/master/upload/catalog/controller/product/product.php#L247-L253
    // the logic uses stock status if quantity <= 0
    // otherwise to default to in stock
    // subtract stock will NOT be used here
    $stock_status = 'in stock';

    // using stock status only if quantity <= 0
    if ($product['quantity'] <= 0) {
      switch ($product['stock_status_id']) {
        // in stock
        // we will default to in stock        
        case 7 :
          break;

        // out of stock
        case 5 :
          $stock_status = 'out of stock';
          break;

        // pre-order
        case 8 :
          $stock_status = 'preorder';
          break;

        // 2-3 Days
        // as Facebook does not support 2-3 Days
        // we will default to in stock
        case 6 :
          break;

        // all other new additions of stock status
        // we will default to in stock
        default :
          break;
      }      
    }
    return $stock_status;
  }

  private function getRetailerProductGroupID($product) {
    // opencart keeps the product to product_group as 1-to-1 mapping
    // relationship, so the same product_id is used for both
    // product and product group
    return $product['product_id'];
  }

  public function getCheckoutUrl($product) {
    return $this->getUrl($product);
  }

  private function getDiscountPrice($registry, $product) {
    $this->model_extension_facebookproduct = $this->facebookcommonutils->loadFacebookProductModel($registry);
    $product_specials = $this->model_extension_facebookproduct->
      getProductSpecials($product['product_id']);

    $product_discount_data = array();
    // initialise the sales price to be same as original price
    // and invalid sales period
    // we cannot set the start date and end date to have the exact timestamp
    // so start date is initialised to 1970/01/17T00:00:00
    // and end date is initialised to 1970/01/17T23:59:00
    $product_discount_data['sale_price_start_date'] =
      self::MIN_DATE . self::MIN_TIME;
    $product_discount_data['sale_price_end_date'] =
      self::MIN_DATE . self::MAX_TIME;
    $product_discount_data['sale_price'] = $this->getPrice($product);
    if ($this->params->hasEnabledSpecialPrice() && $product_specials) {
      foreach ($product_specials as $product_special) {
        // for empty date_start, we will treat it as MIN_DATE
        $sale_start = ($product_special['date_start'] === self::EMPTY_DATE)
          ? self::MIN_DATE . self::MIN_TIME
          : $product_special['date_start'] . self::MIN_TIME;

        // for empty date_end, we will treat it as MAX_DATE
        $sale_end = self::MAX_DATE . self::MAX_TIME;
        if ($product_special['date_end'] !== self::EMPTY_DATE) {
          // the sale end date for Opencart is exclusive of the date
          // so we need to go back to the previous date and set the
          // time to 2359
          $sale_end_date = new DateTime($product_special['date_end']);
          $sale_end_date = $sale_end_date->sub(new DateInterval('P1D'));
          $sale_end = $sale_end_date->format('Y-m-d') . self::MAX_TIME;
        }

        // checks if this sales period is ongoing or will happen in future
        // this means the sale_end is later than current date
        // and sale_end is later than sale_start (sanity check)
        if (strtotime($sale_end) >= time()
          && strtotime($sale_end) >= strtotime($sale_start)) {
          $product_discount_data['sale_price_start_date'] = $sale_start;
          $product_discount_data['sale_price_end_date'] = $sale_end;
          $price_after_tax = $this->getPriceAfterTax(
            $product_special['price'],
            $product['tax_class_id']);
          $product_discount_data['sale_price'] =
            $this->getFormattedPrice($price_after_tax);
          break;
        }
      }
    }

    return $product_discount_data;
  }

  private function getPriceAfterTax($price, $tax_class_id) {
    return $this->params->getTax()->calculate(
      $price,
      $tax_class_id,
      $this->params->getConfigTax());
  }

  protected function formatImageUrl($image_url) {
    // cater for cases where the image_url is a external url
    return (strncmp($image_url, "http://", 7) === 0
      || strncmp($image_url, "https://", 8) === 0)
      ? $image_url
      : $this->getStoreBaseUrl() . "image/" . $image_url;
  }

  public function getAdditionalImageUrls($product) {
    $product_image_urls = $this->params->getModelCatalogProduct()->
      getProductImages($product['product_id']);
    $product_image_urls = array_slice($product_image_urls, 0, 10);
    $product_image_urls = array_map(function($image_url) {
        return $this->formatImageUrl($image_url['image']);
      },
      $product_image_urls);
    return $this->formatAdditionalImageUrls($product_image_urls);
  }

  protected abstract function getFormattedPrice($product);

  protected abstract function formatAdditionalImageUrls($product_image_urls);

  protected abstract function postFormatting($product_data);
}
