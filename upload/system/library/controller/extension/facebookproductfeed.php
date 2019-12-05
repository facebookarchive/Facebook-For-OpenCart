<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

require_once(DIR_APPLICATION.'../system/library/facebookproducttrait.php');

class ControllerExtensionFacebookProductFeed extends Controller {
  use ControllerExtensionFacebookProductTrait;

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
    $this->registry = $registry;
    $this->loadLibrariesForFacebookCatalog();
    $this->model_extension_facebooksetting = $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
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
        $this->registry,
        $product);
    }, $products);
    $this->faeLog->write('Complete - Get Sample product feed');
    return json_encode(array($feed_items), JSON_PRETTY_PRINT);
  }

  private function getWritableProductFeedFolder() {
    // checks on 3 folders if they are writable and return a folder if so
    if(is_writable(DIR_DOWNLOAD)){
      return DIR_DOWNLOAD;
    }
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
    return $product_feed_folder . FacebookCommonUtils::FACEBOOK_FEED_FILENAME;
  }

  private function updateFacebookFeedId($feed_id) {
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $this->model_extension_facebooksetting->updateSettings(
      array(FacebookCommonUtils::FACEBOOK_FEED_ID => $feed_id));
  }

  private function updateFacebookUploadId($upload_id) {
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
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
      $total_num_of_products = $this->model_catalog_product->getTotalProducts(
        array('filter_status' => 1));

      return $this->writeProductFeedFileInBatch($total_num_of_products, $feed_file);
    } catch (Exception $e) {
      // handles any exceptions during the feed file generation
      if (isset($feed_file) && !!($feed_file)) {
        fclose($feed_file);
      }
      $this->faeLog->write(json_encode($e->getMessage()));
      return false;
    }
  }

  private function writeProductFeedFileInBatch($total_number_of_products, $feed_file) {
    $total_batches = ceil($total_number_of_products / FacebookCommonUtils::FACEBOOK_PRODUCT_QUERY_BATCH_COUNT);
    for ($batch_number = 0; $batch_number < $total_batches; $batch_number++) {
      $this->faeLog->write(
        sprintf('Generating product feed file for batch %d', $batch_number));
      $filter_data = array(
        'start' => $batch_number *
          FacebookCommonUtils::FACEBOOK_PRODUCT_QUERY_BATCH_COUNT,
        'limit' => FacebookCommonUtils::FACEBOOK_PRODUCT_QUERY_BATCH_COUNT,
        'filter_status' => 1
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
          $product_data = $this->facebookproductfeedformatter->getProductData(
            $this->registry,
            $product);
          $product_data_as_feed_row =
            $this->convertProductDataAsFeedRow($product_data);
          fwrite($feed_file, $product_data_as_feed_row);
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

  private function checkIsFeedFileStale($filename) {
    $time_file_modified = file_exists($filename) ? filemtime($filename) : 0;

    // if we get no file modified time, or the modified time is 8hours ago,
    // we count it as stale
    if (!$time_file_modified) {
      return true;
    } else {
      return time() - $time_file_modified > 8*3600;
    }
  }

  private function estimateFeedGenerationTimeWithDecay($feed_gen_time){
    // Update feed generation online time estimate w/ 25% decay.
    $facebook_settings = $this->model_extension_facebooksetting->getSettings();
    if (isset(
      $facebook_settings[FacebookCommonUtils::FACEBOOK_FEED_RUNTIME_AVG])) {
        $old_feed_gen_time = $facebook_settings[FacebookCommonUtils::FACEBOOK_FEED_RUNTIME_AVG];
    } else {
      $old_feed_gen_time = 0;
    }

    if($feed_gen_time < $old_feed_gen_time) {
      $feed_gen_time = $feed_gen_time * 0.25 + $old_feed_gen_time * 0.75;
    }
    $this->model_extension_facebooksetting->updateSettings(
      array(FacebookCommonUtils:: FACEBOOK_FEED_RUNTIME_AVG => $feed_gen_time));
  }

  private function sendFileResponse($filename) {
    if(!headers_sent()) {
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="'.basename($filename.'"'));
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Content-Length:'.filesize($filename));

      if(ob_get_level()){
        ob_end_clean();
      }

      readfile($filename, 'rb');

      exit();
    }
  }

  public function genFeed($genNow = false){
		$operation = 'gen feed file';
		$error_data = array('operation' => 'gen feed file');
    $this->faeLog->write('gen feed file');
    
    $start_time = time();

		try {
			// check if feed file path writable
			$productFeedFullFilename = $this->getWritableProductFeedFullFilename();
			if (!$productFeedFullFilename) {
				$this->logError(
					self::FEED_NOT_WRITABLE_ERROR_MESSAGE . $operation,
					$error_data,
					$this->getFeedFolderNotWritableExceptionMessage());
      }
      
      // check if feed file stale
      $isStale = $this->checkIsFeedFileStale($productFeedFullFilename);
      if(!$isStale && !$genNow) {
        $this->faeLog->write('file file is not stale, skip generation');
      } else {
        // remove the file if it exists
        // this is because we are switching to append mode
        // when writing the file and to avoid writing into existing content
        if (is_file($productFeedFullFilename)){
          $this->faeLog->write('gen feed file, remove existing feed file');
          unlink($productFeedFullFilename);
        }

        if (!$this->generateProductFeedFile($productFeedFullFilename)) {
          $this->logError(
          self::FEED_FILE_NOT_GENERATED_ERROR_MESSAGE . $operation,
          $error_data,
          FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE);
        }

        // performs a last check if the feed file is successfully generated
        if (is_file($productFeedFullFilename)) {
          $this->faeLog->write('gen feed file, facebook feed created');
          } else {
          $this->faeLog->write('gen feed file, feed file not created successfully');
          return false;
          }

        $this->faeLog->write('gen feed file, feed file generated');
      }

      $end_time = time();
      $feed_gen_time = $end_time - $start_time;
      $this->faeLog->write(sprintf('feed generation finished, time used: %d seconds', $feed_gen_time));
  
      $this->estimateFeedGenerationTimeWithDecay($feed_gen_time);

      // genFeedPing return time estimation only
      if($this->request->get['from'] == 'genFeedPing') {
        return;
      }

      $this->sendFileResponse($productFeedFullFilename);
		} catch (Exception $e) {
			$this->faeLog->write('Error with gen feed file '.json_encode($e->getMessage()));

			$this->response->addHeader('Content-type: text');
			$this->response->setOutput('There was a problem generating your feed: %s', $e->getMessage());
    }
  }
  
  public function estimateFeedGenerationTime() {
    // Estimate = MAX (Appx Time to Gen 500 Products + 30 , Last Runtime + 20)
    $time_estimate = $this->estimateGenerationTime();
    $time_previous_avg = 0;
    $facebook_settings = $this->model_extension_facebooksetting->getSettings();
    if (isset(
      $facebook_setting[FacebookCommonUtils::FACEBOOK_FEED_RUNTIME_AVG])) {
        $time_previous_avg = $facebook_settings[FacebookCommonUtils::FACEBOOK_FEED_RUNTIME_AVG];
    }

    return max($time_estimate, $time_previous_avg);
  }

  private function estimateGenerationTime() {
    // Appx Time to Gen 500 products + 30
    $total_num_of_products = $this->model_catalog_product->getTotalProducts(array('filter_status' => 1));
    $num_of_samples = $total_num_of_products <= FacebookCommonUtils:: FACEBOOK_THRESHOLD_FOR_DRY_RUN_FEED 
      ? $total_num_of_products 
      : FacebookCommonUtils:: FACEBOOK_THRESHOLD_FOR_DRY_RUN_FEED;
    if($num_of_samples == 0) {
      return FacebookCommonUtils::FACEBOOK_GEN_FEED_BUFFER_TIME;
    }
    
    $feed_dryrun_filename = $this->getWritableProductFeedFolder() . FacebookCommonUtils::FACEBOOK_FEED_DRYRUN_FILENAME;
    $feed_dryrun = fopen($feed_dryrun_filename, 'ab');
    $start_time = time();
    $this->writeProductFeedFileInBatch($num_of_samples, $feed_dryrun);
    $end_time = time();

    $time_spent = $end_time - $start_time;

    // Estimated Time =
    // 150% of Linear extrapolation of the time to generate 500 products
    // + 30 seconds of buffer time.
    $time_estimate = $time_spent * $total_num_of_products / $num_of_samples * 1.5 + FacebookCommonUtils::FACEBOOK_GEN_FEED_BUFFER_TIME;
    $this->faeLog->write('Feed Generation Time Estimate: '. $time_estimate);
    return $time_estimate;
  }

  public function getTotalEnabledProducts($data = array()) {
    return $this->model_catalog_product->getTotalProducts(
      array('filter_status' => 1));
  }
}
