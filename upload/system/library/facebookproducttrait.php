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
    $this->facebookcommonutils = new FacebookCommonUtils();
    $this->model_extension_facebookproduct =
      $this->facebookcommonutils->loadFacebookProductModel($this->registry);
    $this->facebookgraphapierror = new FacebookGraphAPIError();
    $this->facebookgraphapi = new FacebookGraphAPI();
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
    $enable_special_price =
      ($this->config->get('facebook_enable_special_price') === 'true')
      ? true
      : false;

    $params = new FacebookProductFormatterParams(array(
      'configTax' => $config_tax,
      'currencyCode' => $default_currency_code,
      'enableSpecialPrice' => $enable_special_price,
      'hasCents' => $has_cents,
      'modelCatalogProduct' => $this->model_catalog_product,
      'storeName' => $store_name,
      'tax' => $this->facebooktax));

    $this->facebookproductfeedformatter->setup($params);
    $this->facebooksampleproductfeedformatter->setup($params);
  }

  private function getFacebookPageAccessToken() {
    return $this->getFacebookSetting(FacebookCommonUtils::FACEBOOK_PAGE_TOKEN);
  }

  private function getFacebookFeedId() {
    return $this->getFacebookSetting(FacebookCommonUtils::FACEBOOK_FEED_ID);
  }

  private function getFacebookExternalMerchantSettings() {
    return $this->getFacebookSetting(
      FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID);
  }

  private function getFacebookSetting($setting_key) {
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $facebook_setting = $this->model_extension_facebooksetting->
      getSettings();
    return (isset($facebook_setting[$setting_key]))
      ? $facebook_setting[$setting_key]
      : null;
  }

  private function deleteFacebookSetting($setting_key) {
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
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
    // throws exception if exception message is passed in
    if ($exception_message) {
      throw new Exception($exception_message);
    }
  }

  // this method validates that FAE and catalog setup is complete
  // it does not check that the product upload is complete
  public function validateFAEAndCatalogSetup(
    $operation,
    $error_data = array('operation' => 'Access product module')) {
    // we are not using the getXXX methods to avoid repeated retrieval from DB
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $facebook_setting = $this->model_extension_facebooksetting->
      getSettings();

    // Two step verification

    // 1. Verify if the FAE setup is done
    if (!isset(
      $facebook_setting[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])) {
      // FAE not setup so unable to log to API endpoint
      throw new Exception(
        FacebookCommonUtils::FAE_NOT_SETUP_EXCEPTION_MESSAGE);
    }

    // 2. Verify if catalog id exists
    $catalog_id =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_CATALOG_ID])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_CATALOG_ID]
      : null;
    if (!$catalog_id) {
      // log to local error log and throw exception
      $this->faeLog->write(
        FacebookCommonUtils::NO_CATALOG_ID_PAGE_ID_ACCESS_TOKEN_ERROR_MESSAGE .
        $operation);
      throw new Exception(FacebookCommonUtils::INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE);
    }
  }

}
