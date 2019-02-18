<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

require_once(DIR_APPLICATION.'../admin/controller/extension/facebookproducttrait.php');

class ControllerExtensionFacebookProductFeed extends Controller {
  use ControllerExtensionFacebookProductTrait;

  const CATALOG_FEED_FILENAME = 'fae_product_catalog.csv';
  const FEED_NAME =
    'Initial product sync from OpenCart. DO NOT DELETE.';

  const FEED_NOT_WRITABLE_ERROR_MESSAGE =
    'Failure - folder is not writable';
  const FEED_FILE_NOT_GENERATED_ERROR_MESSAGE =
    'Failure - feed file not generated';
  const FEED_NOT_CREATED_ERROR_MESSAGE =
    'Failure - facebook feed not created';
  const UPLOAD_NOT_CREATED_ERROR_MESSAGE =
    'Failure - facebook upload not created';

  const INITIAL_PRODUCT_SYNC_STATUS_SUCCESS = 'success';
  const INITIAL_PRODUCT_SYNC_STATUS_IN_PROGRESS = 'in_progress';

  public function __construct($registry) {
    parent::__construct($registry);
    $this->loadLibrariesForFacebookCatalog();
  }

  // this is a backend controller for syncing facebook products via feed,
  // so there is no frontend UI involved
  public function index() {
  }

  public function getSampleProductFeed() {
    $this->faeLog->write('Get Sample product feed');
    $filter_data = array(
      'start'           => 0,
      'limit'           => 12,
    );
    $products = $this->model_extension_facebookproduct->
      getProducts($filter_data);
    $feed_items = array_map(function($product) {
      return $data = $this->facebooksampleproductfeedformatter->getProductData(
        $product);
    }, $products);
    $this->faeLog->write('Complete - Get Sample product feed');
    return json_encode(array($feed_items), JSON_PRETTY_PRINT);
  }

  public function syncAllProductsUsingFeed() {
    $operation = ', sync all products using feed';
    $error_data = array('operation' => 'Sync all products using feed');
    $this->faeLog->write('Sync all products using feed');

    $facebook_catalog_id = $this->getFacebookCatalogId();
    $facebook_page_token = $this->getFacebookPageAccessToken();
    if (!$facebook_catalog_id || !$facebook_page_token) {
      $this->logError(
        FacebookCommonUtils::NO_CATALOG_ID_PAGE_ID_ACCESS_TOKEN_ERROR_MESSAGE .
          $operation,
        $error_data,
        FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE);
    }

    try {
      $productFeedFullFilename = $this->getWritableProductFeedFullFilename();
      if (!$productFeedFullFilename) {
        $this->logError(
          self::FEED_NOT_WRITABLE_ERROR_MESSAGE . $operation,
          $error_data,
          $this->getFeedFolderNotWritableExceptionMessage());
      }

      // remove the file if it exists
      // this is because we are switching to append mode
      // when writing the file and to avoid writing into existing content
      if (is_file($productFeedFullFilename)) {
        $this->faeLog->write(
          'Sync all products using feed, remove existing feed file');
        unlink($productFeedFullFilename);
      }

      if (!$this->generateProductFeedFile($productFeedFullFilename)) {
        $this->logError(
          self::FEED_FILE_NOT_GENERATED_ERROR_MESSAGE . $operation,
          $error_data,
          FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE);
      }

      $this->faeLog->write('Sync all products using feed, feed file generated');

      $feed_id = $this->createFeed(
        $facebook_catalog_id,
        $facebook_page_token);
      if (!$feed_id) {
        $this->logError(
          self::FEED_NOT_CREATED_ERROR_MESSAGE . $operation,
          $error_data,
          FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE);
      }

      // performs a last check if the feed file is successfully generated
      if (is_file($productFeedFullFilename)) {
        $this->faeLog->write(
          'Sync all products using feed, facebook feed created');
      } else {
        $this->faeLog->write(
          'Sync all products using feed, feed file not created successfully');
        return false;
      }

      $upload_id = $this->createUpload(
        $feed_id,
        $facebook_page_token,
        $productFeedFullFilename);
      if (!$upload_id) {
        $this->logError(
          self::UPLOAD_NOT_CREATED_ERROR_MESSAGE . $operation,
          $error_data,
          FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE);
      }
      $this->faeLog->write(
        'Sync all products using feed, facebook upload created');

      unlink($productFeedFullFilename);

      // performs a final check to ensure the feed sync has correctly setup
      $this->validateFAEAndCatalogSetup($operation, $error_data);

      $this->faeLog->write('Complete - Sync all products using feed');
      return array('success' => 'true');
    } catch (Exception $e) {
      $this->faeLog->write(
        'Error with syncing all products with feed ' .
        json_encode($e->getMessage()));
      return array ('success' => 'false');
    }
  }

  private function getWritableProductFeedFolder() {
    // checks on 2 folders if they are writable and return a folder if so
    if (is_writable(DIR_MODIFICATION)) {
      return DIR_MODIFICATION;
    }
    if (is_writable(DIR_LOGS)) {
      return DIR_LOGS;
    }
    return false;
  }

  private function getWritableProductFeedFullFilename() {
    $product_feed_folder = $this->getWritableProductFeedFolder();
    if (!$product_feed_folder) {
      return null;
    }
    return $product_feed_folder . self::CATALOG_FEED_FILENAME;
  }

  private function updateFacebookFeedId($feed_id) {
    $this->loadFacebookModel('extension/facebooksetting');
    $this->model_extension_facebooksetting->updateSettings(
      array(FacebookCommonUtils::FACEBOOK_FEED_ID => $feed_id));
  }

  private function updateFacebookUploadId($upload_id) {
    $this->loadFacebookModel('extension/facebooksetting');
    $this->model_extension_facebooksetting->updateSettings(
      array(FacebookCommonUtils::FACEBOOK_UPLOAD_ID => $upload_id));
  }

  private function generateProductFeedFile($productFeedFilename) {
    $this->faeLog->write('Generating product feed file');

    try {
      // opens up the feed file and close inside the main method
      // to avoid the extra overhead of file opening and closing
      error_log('feed file = ' . $productFeedFilename);
      $feed_file = fopen($productFeedFilename, "ab");

      $this->faeLog->write('Generating product feed file header');
      if (!$this->writeProductFeedFileHeader($feed_file)) {
        // something wrong happened, return false
        fclose($feed_file);
        $this->faeLog->write('Unable to generate the product feed file header');
        return false;
      }

      $this->loadLibrariesForFacebookCatalog();
      // queries and writes the products in batches
      // this is to handle for large product catalogs
      $total_products = $this->model_catalog_product->getTotalProducts(
        array('filter_status' => 1));
      $total_batches = (int)($total_products / FacebookCommonUtils::FACEBOOK_PRODUCT_QUERY_BATCH_COUNT) + 1;
      for ($batch_number = 0; $batch_number <= $total_batches; $batch_number++) {
        $this->faeLog->write(
          sprintf('Generating product feed file for batch %d', $batch_number));
        $filter_data = array(
          'start' => $batch_number *
            FacebookCommonUtils::FACEBOOK_PRODUCT_QUERY_BATCH_COUNT,
          'limit' => FacebookCommonUtils::FACEBOOK_PRODUCT_QUERY_BATCH_COUNT
        );
        $products =
          $this->model_extension_facebookproduct->getProducts($filter_data);
        if (isset($products) && sizeof($products) > 0) {
          if (!$this->writeProductFeedFile(
            $products,
            $feed_file)) {
            // something wrong happened, return false
            $this->faeLog->write(sprintf(
              'Error with generating product feed file for batch %d',
              $batch_number));
            fclose($feed_file);
            return false;
          }
        }
      }

      // feed file is generated successfully
      fclose($feed_file);

      return true;
    } catch (Exception $e) {
      // handles any exceptions during the feed file generation
      if (isset($feed_file) && !!($feed_file)) {
        fclose($feed_file);
      }
      $this->faeLog->write(json_encode($e->getMessage()));
      return false;
    }
  }

  private function writeProductFeedFileHeader($feed_file) {
    try {
      fputs($feed_file, "\xEF\xBB\xBF");
      fwrite($feed_file, $this->getProductFeedHeaderRow());
      return true;
    } catch (Exception $e) {
      $this->faeLog->write(json_encode($e->getMessage()));
      return false;
    }
  }

  private function writeProductFeedFile(
    $products,
    $feed_file) {
    try {
      array_walk(
        $products,
        function($product) use($feed_file) {
          // only sends in the products which are enabled, status = 1
          if ($product['status']) {
            $product_data = $this->facebookproductfeedformatter->getProductData(
              $product);
            $product_data_as_feed_row =
              $this->convertProductDataAsFeedRow($product_data);
            fwrite($feed_file, $product_data_as_feed_row);
          }
        });
      return true;
    } catch (Exception $e) {
      $this->faeLog->write(json_encode($e->getMessage()));
      return false;
    }
  }

  private function getProductFeedHeaderRow() {
    return 'id,title,description,image_link,link,google_product_category,' .
      'brand,price,currency,availability,item_group_id,checkout_url,' .
      'additional_image_link,sale_price_effective_date,' .
      'sale_price,condition' . PHP_EOL;
  }

  private function convertProductDataAsFeedRow($product_data) {
    return
      $product_data['retailer_id'] . ',' .
      $product_data['name'] . ',' .
      $product_data['description'] . ',' .
      $product_data['image_url'] . ',' .
      $product_data['url'] . ',' .
      $product_data['category'] . ',' .
      $product_data['brand'] . ',' .
      $product_data['price'] . ',' .
      $product_data['currency'] . ',' .
      $product_data['availability'] . ',' .
      $product_data['retailer_product_group_id'] . ',' .
      $product_data['checkout_url'] . ',' .
      $product_data['additional_image_urls'] . ',' .
      $product_data['sale_price_period'] . ',' .
      $product_data['sale_price'] . ',' .
      $product_data['condition'] . PHP_EOL;
  }

  private function createFeed(
    $facebook_catalog_id,
    $facebook_page_token) {
    $result = $this->facebookgraphapi->createFeed(
      $facebook_catalog_id,
      array('name' => self::FEED_NAME),
      $facebook_page_token);
    if (!isset($result['id']) || !$result['id']) {
      $this->faeLog->write(json_encode($result));
      return null;
    }
    $feed_id = $result['id'];
    $this->updateFacebookFeedId($feed_id);
    return $feed_id;
  }

  private function createUpload(
    $facebook_feed_id,
    $facebook_page_token,
    $productFeedFullFilename) {
    $result = $this->facebookgraphapi->createUpload(
      $facebook_feed_id,
      $productFeedFullFilename,
      $facebook_page_token);
    if (!isset($result['id']) || !$result['id']) {
      $this->faeLog->write(json_encode($result));
      return null;
    }
    $upload_id = $result['id'];
    $this->updateFacebookUploadId($upload_id);
    return $upload_id;
  }

  public function getInitialProductSyncStatus() {
    $this->loadLibrariesForFacebookCatalog();
    $operation = ', get initial product sync status';
    $error_data = array('operation' => 'Get initial product sync status');

    // do not perform the validate for upload time
    // as this is called from the FAE setup screen, so the
    // product feed upload may still be ongoing
    $this->validateFAEAndCatalogSetup(
      $operation,
      $error_data);

    $facebook_page_token = $this->getFacebookPageAccessToken();
    // Verify if the upload end time is tracked in the settings
    // if upload end time is present, will assume everything is ok
    $this->loadFacebookModel('extension/facebooksetting');
    $facebook_setting =
      $this->model_extension_facebooksetting->getSettings();
    if (isset(
      $facebook_setting[FacebookCommonUtils::FACEBOOK_UPLOAD_END_TIME])) {
      return array('status' => self::INITIAL_PRODUCT_SYNC_STATUS_SUCCESS);
    }

    // Verify on FB on the status of the upload
    // if upload end time is present, we will store it in local DB
    // so that subsequent verification will stop at step 3
    $result = $this->facebookgraphapi->getUploadStatus(
      $facebook_setting[FacebookCommonUtils::FACEBOOK_UPLOAD_ID],
      $facebook_page_token);
    if (isset($result['end_time'])) {
      $this->updateFacebookUploadEndTime($result['end_time']);
      return array('status' => self::INITIAL_PRODUCT_SYNC_STATUS_SUCCESS);
    } else {
      return array(
        'status' => self::INITIAL_PRODUCT_SYNC_STATUS_IN_PROGRESS);
    }
  }

  private function updateFacebookUploadEndTime($end_time) {
    $this->loadFacebookModel('extension/facebooksetting');
    $this->model_extension_facebooksetting->updateSettings(
      array(FacebookCommonUtils::FACEBOOK_UPLOAD_END_TIME => $end_time));
  }

  public function isWritableProductFeedFolderAvailable() {
    if ($this->getWritableProductFeedFolder()) {
      return true;
    } else {
      throw new Exception($this->getFeedFolderNotWritableExceptionMessage());
    }
  }

  private function getFeedFolderNotWritableExceptionMessage() {
    return sprintf('We need the %s ' .
      'folder on your server to be writable to allow for ' .
      'initial product upload. Please enable the folder to ' .
      'be writable and try again.',
      DIR_MODIFICATION);
  }
}
