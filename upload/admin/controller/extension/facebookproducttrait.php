<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

trait ControllerExtensionFacebookProductTrait {
  private $faeLog;

  private function loadLibrariesForFacebookCatalog() {
    $this->faeLog = new Log(FacebookCommonUtils::FAE_LOG_FILENAME);
    $this->load->model('catalog/product');
    $this->load->model('localisation/currency');
    $this->loadFacebookModel('extension/facebookproduct');
    $this->facebookcommonutils = new FacebookCommonUtils();
    $this->facebookgraphapierror = new FacebookGraphAPIError();
    $this->facebookgraphapi = new FacebookGraphAPI();
    $this->facebookproductapiformatter = new FacebookProductAPIFormatter();
    $this->facebookproductfeedformatter = new FacebookProductFeedFormatter();
    $this->facebooksampleproductfeedformatter = new FacebookSampleProductFeedFormatter();
    $this->facebooktax = new FacebookTax($this->registry);

    $config_tax = $this->config->get('config_tax');
    $default_currency_code = $this->config->get('config_currency');
    $default_currency = $this->model_localisation_currency->getCurrencyByCode(
      $default_currency_code);
    $has_cents = $this->facebookcommonutils->doesDefaultCurrencySupportCents(
        $default_currency_code,
        $default_currency);
    $store_name = $this->config->get('config_name');

    $params = new FacebookProductFormatterParams(array(
      'configTax' => $config_tax,
      'currencyCode' => $default_currency_code,
      'hasCents' => $has_cents,
      'modelCatalogProduct' => $this->model_catalog_product,
      'storeName' => $store_name,
      'tax' => $this->facebooktax));

    $this->facebookproductapiformatter->setup($params);
    $this->facebookproductfeedformatter->setup($params);
    $this->facebooksampleproductfeedformatter->setup($params);
  }

  private function loadFacebookModel($model_name) {
    // attempting to load the model if it is on the same folder path
    $full_model_filename =
      getcwd() . "/model/" . $model_name . ".php";
    $is_facebook_model_loaded = false;
    if (is_file($full_model_filename)) {
      try {
        $this->load->model($model_name);
        $is_facebook_model_loaded = true;
      } catch (Exception $e) {
        $is_facebook_model_loaded = false;
      }
    }

    // unable to load the model
    // this will happen for common models which are placed in
    // the admin folder and shared/re-used in catalog folder (store front)
    // in this case we will explicitly load the full name of the model
    if (!$is_facebook_model_loaded) {
      require_once
        DIR_APPLICATION . "../admin/model/" . $model_name . ".php";
      switch ($model_name) {
        case "extension/facebooksetting":
          $this->model_extension_facebooksetting =
            new ModelExtensionFacebookSetting($this->registry);
          break;
        case "extension/facebookproduct":
          $this->model_extension_facebookproduct =
            new ModelExtensionFacebookProduct($this->registry);
          break;
      }
    }
  }

  private function getFacebookCatalogId() {
    return $this->getFacebookSetting(FacebookCommonUtils::FACEBOOK_CATALOG_ID);
  }

  private function getFacebookPageAccessToken() {
    return $this->getFacebookSetting(FacebookCommonUtils::FACEBOOK_PAGE_TOKEN);
  }

  private function getFacebookExternalMerchantSettings() {
    return $this->getFacebookSetting(
      FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID);
  }

  private function getFacebookPageId() {
    return $this->getFacebookSetting(FacebookCommonUtils::FACEBOOK_PAGE_ID);
  }

  private function getFacebookSetting($setting_key) {
    $this->loadFacebookModel('extension/facebooksetting');
    $facebook_setting = $this->model_extension_facebooksetting->
      getSettings();
    return (isset($facebook_setting[$setting_key]))
      ? $facebook_setting[$setting_key]
      : null;
  }

  private function deleteFacebookSetting($setting_key) {
    $this->loadFacebookModel('extension/facebooksetting');
    $facebook_setting =
      $this->model_extension_facebooksetting->
        deleteSetting($setting_key);
  }

  private function logError(
    $error_message,
    $error_data,
    $exception_message = null) {
    // logs to local log file
    $this->faeLog->write($error_message);
    // logs to Facebook FAE error end point if access token
    // and merchant settings id are available
    if ($this->getFacebookExternalMerchantSettings()
      && $this->getFacebookPageAccessToken()) {
      $fb_error_log_result = $this->logErrorToFacebook(
        $error_message,
        $error_data);
      $this->faeLog->write(json_encode($fb_error_log_result));
    }
    // throws exception if exception message is passed in
    if ($exception_message) {
      throw new Exception($exception_message);
    }
  }

  private function logErrorToFacebook($error_message, $error_data) {
    $error_data['opencart_plugin_version'] =
      $this->facebookcommonutils->getPluginVersion();
    $error_data['opencart_version'] = VERSION;
    $error_data['php_version'] = phpversion();
    return $this->facebookgraphapi->fblog(
      $this->getFacebookExternalMerchantSettings(),
      $this->getFacebookPageAccessToken(),
      $error_message,
      $error_data,
      true);
  }

  // this method validates that FAE and catalog setup is complete
  // it does not check that the product upload is complete
  public function validateFAEAndCatalogSetup(
    $operation,
    $error_data = array('operation' => 'Access product module')) {
    // we are not using the getXXX methods to avoid repeated retrieval from DB
    $this->loadFacebookModel('extension/facebooksetting');
    $facebook_setting = $this->model_extension_facebooksetting->
      getSettings();

    // Five step verification

    // 1. Verify if the FAE setup is done
    if (!isset(
      $facebook_setting[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])) {
      // FAE not setup so unable to log to API endpoint
      throw new Exception(
        FacebookCommonUtils::FAE_NOT_SETUP_EXCEPTION_MESSAGE);
    }

    // 2. Verify if the access token, page and catalog id exists
    $facebook_page_token =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_PAGE_TOKEN])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_PAGE_TOKEN]
      : null;
    $catalog_id =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_CATALOG_ID])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_CATALOG_ID]
      : null;
    $page_id = isset($facebook_setting[FacebookCommonUtils::FACEBOOK_PAGE_ID])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_PAGE_ID]
      : null;
    if (!$catalog_id || !$page_id || !$facebook_page_token) {
      $this->logError(
        FacebookCommonUtils::NO_CATALOG_ID_PAGE_ID_ACCESS_TOKEN_ERROR_MESSAGE .
          $operation,
        $error_data,
        FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE);
    }

    // 3. Verify if the access token is valid
    try {
      $this->isAccessTokenValid($page_id, $facebook_page_token);
    } catch (Exception $e) {
      // not using the error_log as the access token is not valid,
      // hence cant log to fb endpoint
      $this->faeLog->write("Invalid access token when querying FB page");
      throw new Exception(
        FacebookCommonUtils::ACCESS_TOKEN_INVALID_EXCEPTION_MESSAGE);
    }

    // 4. Verify if feed id is present
    if (!isset($facebook_setting[FacebookCommonUtils::FACEBOOK_FEED_ID])) {
      $exception_message = $this->getExceptionMessageDueToProductSyncError();
      $this->logError(
        FacebookCommonUtils::FEED_NOT_CREATED_ERROR_MESSAGE . $operation,
        $error_data,
        $exception_message);
    }

    // 5. Verify if upload id is present
    if (!isset($facebook_setting[FacebookCommonUtils::FACEBOOK_UPLOAD_ID])) {
      $exception_message = $this->getExceptionMessageDueToProductSyncError();
      $this->logError(
        FacebookCommonUtils::UPLOAD_NOT_CREATED_ERROR_MESSAGE . $operation,
        $error_data,
        $exception_message);
    }
  }

  private function getExceptionMessageDueToProductSyncError() {
    $exception_message =
      FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE;
    // we will show an extra message indicating if the product count is large
    $product_count = $this->model_catalog_product->getTotalProducts(
      array('filter_status' => 1));
    if ($product_count > FacebookCommonUtils::PRODUCT_COUNT_THRESHOLD) {
      $exception_message = $exception_message .
        '<br/>' .
        sprintf(
          FacebookCommonUtils::LARGE_PRODUCT_CATALOG_EXCEPTION_MESSAGE,
          $product_count);
    }
    return $exception_message;
  }

  // checks if the product upload is completed
  // by verifying that the upload_end_time is available
  // for that upload session, and if it is available,
  // we want to save it into the local DB so that we
  // do not need to query fb server in future
  public function isProductUploadComplete($facebook_setting) {
    if (isset($facebook_setting[
      FacebookCommonUtils::FACEBOOK_UPLOAD_END_TIME])) {
      return true;
    }

    $result = $this->facebookgraphapi->getUploadStatus(
      $facebook_setting[FacebookCommonUtils::FACEBOOK_UPLOAD_ID],
      $facebook_setting[FacebookCommonUtils::FACEBOOK_PAGE_TOKEN]);
    if (isset($result['end_time'])) {
      $this->model_extension_facebooksetting->updateSettings(
        array(FacebookCommonUtils::FACEBOOK_UPLOAD_END_TIME =>
          $result['end_time']));
      return true;
    } else {
      return false;
    }
  }

  // this method validates that FAE and catalog setup is complete
  // and also validates that the product upload is complete
  // this method will be called from product management module
  // as we want to show a warning notification to user that
  // the product sync is still ongoing
  public function validateFAEAndCatalogSetupAndProductUploadComplete(
    $operation,
    $error_data = array('operation' => 'Access product module')) {
    $this->validateFAEAndCatalogSetup($operation, $error_data);
    $facebook_setting = $this->model_extension_facebooksetting->
      getSettings();
    if (!$this->isProductUploadComplete($facebook_setting)) {
      $this->logError(
        FacebookCommonUtils::UPLOAD_IN_PROGRESS_ERROR_MESSAGE . $operation,
        $error_data,
        FacebookCommonUtils::PRODUCT_SYNC_EXCEPTION_MESSAGE);
    }
  }

  private function isAccessTokenValid($page_id, $facebook_page_token) {
    $result = $this->facebookgraphapi->getFacebookPageId(
      $page_id,
      $facebook_page_token);
    if (!isset($result['id'])) {
      // logs the response if the page id is not returned
      $this->faeLog->write(
        "Response from FB page query does not contain id result "
        . json_encode($result));
      throw new Exception();
    }
    return (isset($result['id']));
  }
}
