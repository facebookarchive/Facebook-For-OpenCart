<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

require_once DIR_SYSTEM . 'library/vendor/autoload.php';

use FacebookAds\Object\ServerSide\AdsPixelSettings;

class DAPixelConfigParams {
  private $eventName; // pixel event name to be fired
  private $products; // products to be used to generate the DA pixel params
  private $currency; // object to convert price into specified currency
  private $currencyCode; // currency code for this event
  private $hasQuantity; // 1 = has quantity, 0 = quantity default to 1
                         // some events needs quantity, eg Purchase
                         // while others so not need quantity, eg Search
  private $isCustomEvent; // 1 = custom event, 0 = standard event
  private $paramNameUsedInProductListing; // eg content_category, search
  private $paramValueUsedInProductListing; // value of the param name

  public function __construct($params) {
    if (isset($params['eventName'])) {
      $this->eventName = $params['eventName'];
    }
    if (isset($params['isCustomEvent'])) {
      $this->isCustomEvent = $params['isCustomEvent'];
    }
    $this->products = (isset($params['products']))
      ? $params['products']
      : array();
    $this->products = ($this->products)
      ? $this->products
      : array();
    if (isset($params['currency'])) {
      $this->currency = $params['currency'];
    }
    if (isset($params['currencyCode'])) {
      $this->currencyCode = $params['currencyCode'];
    }
    if (isset($params['hasQuantity'])) {
      $this->hasQuantity = $params['hasQuantity'];
    }
    if (isset($params['paramNameUsedInProductListing'])) {
      $this->paramNameUsedInProductListing =
        $params['paramNameUsedInProductListing'];
    }
    if (isset($params['paramValueUsedInProductListing'])) {
      $this->paramValueUsedInProductListing =
        $params['paramValueUsedInProductListing'];
    }
  }

  public function getEventName() {
    return $this->eventName;
  }

  public function getProducts() {
    return $this->products;
  }

  public function getCurrency() {
    return $this->currency;
  }

  public function getCurrencyCode() {
    return $this->currencyCode;
  }

  public function hasQuantity() {
    return $this->hasQuantity;
  }

  public function isCustomEvent() {
    return $this->isCustomEvent;
  }

  public function getParamNameUsedInProductListing() {
    return $this->paramNameUsedInProductListing;
  }

  public function getParamValueUsedInProductListing() {
    return $this->paramValueUsedInProductListing;
  }
}

class FacebookCommonUtils {
  const FAE_LOG_FILENAME = 'facebook_ads_extension.log';
  const FACEBOOK_DIA_SETTING_ID = 'facebook_dia_setting_id';
  const FACEBOOK_PIXEL_ID = 'facebook_pixel_id';
  const FACEBOOK_PIXEL_USE_PII = 'facebook_pixel_use_pii';
  const FACEBOOK_PIXEL_ENABLED_AAM_FIELDS = 'facebook_pixel_enabled_aam_fields';
  const FACEBOOK_PIXEL_SIGNATURE = 'facebook_pixel_signature';
  const FACEBOOK_CATALOG_ID = 'facebook_catalog_id';
  const FACEBOOK_PAGE_ID = 'facebook_page_id';
  const FACEBOOK_PAGE_TOKEN = 'facebook_page_token';
  const FACEBOOK_FEED_ID = 'facebook_feed_id';
  const FACEBOOK_FEED_FILENAME = 'fbe_product_catalog.csv';
  const FACEBOOK_FEED_RUNTIME_AVG = 'facebook_feed_runtime_avg';
  const FACEBOOK_FEED_DRYRUN_FILENAME = 'fbe_feed_dryrun.txt';
  const FACEBOOK_FEED_MIGRATED = 'facebook_feed_migrated';
  const FACEBOOK_UPLOAD_ID = 'facebook_upload_id';
  const FACEBOOK_UPLOAD_END_TIME = 'facebook_upload_end_time';
  const FACEBOOK_MESSENGER = 'facebook_messenger_activated';
  const FACEBOOK_JSSDK_VER = 'facebook_jssdk_version';
  const FACEBOOK_CUSTOMIZATION_LOCALE = 'facebook_customization_locale';
  const FACEBOOK_LATEST_RELEASE_URL =
    'https://api.github.com/repos/facebookincubator/Facebook-for-OpenCart/releases/latest';
  const FACEBOOK_LAST_UPGRADE_CHECK_TIME = 'facebook_last_upgrade_check_time';
  const FACEBOOK_LAST_AAM_CHECK_TIME = 'facebook_last_aam_check_time';
  const FACEBOOK_ENABLE_COOKIE_BAR = 'facebook_enable_cookie_bar';
  const FACEBOOK_PIXEL_CODE_INDICATOR = 'window.isFacebookPixelAdded=1;';
  const FACEBOOK_MESSENGER_CHAT_CODE_INDICATOR = 'window.isFacebookCustomerChatAdded=1;';
  const FACEBOOK_ENABLE_SPECIAL_PRICE = 'facebook_enable_special_price';
  const FACEBOOK_SYSTEM_USER_ACCESS_TOKEN = 'facebook_system_user_access_token';
  const FACEBOOK_FBE_V2_INSTALLED = 'facebook_fbe_v2_installed';
  const FACEBOOK_USE_S2S = 'facebook_use_s2s';
  const OPENCART_SERVER_BASE_URL = 'https://facebook.opencart.com';
  const OPENCART_FBE_IFRAME_PATH = '/';
  const OPENCART_FACEBOOK_APP_ID = '785409108588782';

  const FACEBOOK_THRESHOLD_FOR_INITIAL_SYNC_BY_API = 1000;
  const PRODUCT_COUNT_THRESHOLD = 5000;

  const FACEBOOK_PRODUCT_QUERY_BATCH_COUNT = 100;
  const FACEBOOK_THRESHOLD_FOR_DRY_RUN_FEED = 500;

  const FACEBOOK_GEN_FEED_BUFFER_TIME = 30;

  const NO_CATALOG_ID_PAGE_ID_ACCESS_TOKEN_ERROR_MESSAGE =
    'Failure - no catalog';
  const FEED_NOT_CREATED_ERROR_MESSAGE =
    'Failure - facebook feed not created';
  const UPLOAD_NOT_CREATED_ERROR_MESSAGE =
    'Failure - facebook upload not created';
  const UPLOAD_IN_PROGRESS_ERROR_MESSAGE =
    'Failure - facebook upload in progress';

  const FAE_NOT_SETUP_EXCEPTION_MESSAGE =
    'You have yet to setup Facebook Business Extension. Please click on Facebook Business Extension and Get Started.';
  const INITIAL_PRODUCT_SYNC_EXCEPTION_MESSAGE =
    'There is an error with Facebook Business Extension setup. Click on Facebook Business Extension, Manage Settings, go to Advanced options and click on Delete Settings to restart the setup.<br/>Please contact Facebook via our <a href="https://github.com/facebookincubator/Facebook-For-OpenCart/issues" target="_blank">Github</a> if this error keeps showing.';
  const LARGE_PRODUCT_CATALOG_EXCEPTION_MESSAGE = 'The sync failure of your products to Facebook may be because of the large number of products on your system (We detected %d products). Please refer to our <a href="https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/FAQ.md#syncing-of-opencart-products-to-facebook-catalog" target="_blank">FAQ</a> section on "Syncing of OpenCart products to Facebook catalog" for more details.';
  const ACCESS_TOKEN_INVALID_EXCEPTION_MESSAGE =
    'There is an error making API calls using the access token. The error encountered is "%s".<br/>To update your access token, please click on Facebook Business Extension, Manage Settings, go to Advanced options and click on Update token.';
  const PRODUCT_SYNC_EXCEPTION_MESSAGE =
    'The product sync on Facebook catalog is still ongoing. Please wait for the sync to complete before making any product changes.';
  const REQUEST_PIXEL_SIGNATURE_ERROR_MESSAGE = 'There is an error requesting a signature key for your pixel, please try again later. Please contact Facebook via our <a href="https://github.com/facebookincubator/Facebook-For-OpenCart/issues" target="_blank">Github</a> if this error keeps showing up.';
  const PLUGIN_UPGRADE_MESSAGE = 'A newer version of the Facebook Business Extension plugin is available. To download it, go to <a href="https://github.com/facebookincubator/Facebook-For-OpenCart/releases" target="_blank">Github</a> or <a href="https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=32336" target="_blank">OpenCart marketplace</a>.';
  const MISSING_WEB_STORE_CODE_ERROR_MESSAGE = 'We have detected the %s is not correctly setup in your web store site. You can try these below steps to fix the problem.<br/>1. Ensure that you have given read+write permissions to the admin, catalog and system folders on your OpenCart webserver<br/>2. Ensure that you have Refresh the modifications, <a href="https://drive.google.com/open?id=1qy-ipwK1HCk8oSnUmGuy6MCJxQdUyGfw" target="_blank">View steps</a><br/>3. For OpenCart 3.x, ensure that you have disabled the theme and SASS cache, <a href="https://drive.google.com/open?id=1bY-bworYxX36b88HDvFW0_32C3Wtq_Tm" target="_blank">View steps</a><br/>4. For OpenCart 3.x, ensure that you do not have modifications to the header design, <a href="https://drive.google.com/open?id=1066BSKAqjKegzw-5oKuvtZuzu_PzkRZT" target="_blank">View steps</a><br/>';
  const FACEBOOK_FEED_MIGRATED_AND_WEBSITE_IN_MAINTENANCE_MESSAGE = 'Warning: We have detected that your web store is in maintenance mode. This will cause the Facebook catalog scheduled sync to fail. To disable the maintenance mode, access Settings, click on your Store, select Server tab and choose No for Maintenance mode.';
  const FACEBOOK_CONFIGURE_S2S_MESSAGE =
    'The Facebook Business Extension now includes support for the Conversion API, which lets you send events directly from your website\'s server. Click button below to setup. Refer to the <a href="https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE.md#setup-for-facebook-business-manager-page-pixel-and-catalog" target="_blank">installation guide</a> for more details.';

  const ACCESS_TOKEN_INVALID_EXCEPTION_CODE = 452;

  private $pluginAgentName = 'exopencart';
// system auto generated, DO NOT MODIFY
private $pluginVersion = '3.1.1';
// system auto generated, DO NOT MODIFY

  public function __construct() {

  }

  public function getPluginVersion() {
    return $this->pluginVersion;
  }

  public function getAgentString() {
    $plugin_agent_name = $this->getPluginAgentName();
    $opencart_version = VERSION;
    $plugin_version = $this->getPluginVersion();

    $agent_string = sprintf(
      '%s-%s-%s',
      $plugin_agent_name,
      $opencart_version,
      $plugin_version);

    return $agent_string;
  }

  public function getProperFormattedString($text) {
    if ((bool)$text) {
      return trim(strip_tags(html_entity_decode(
        html_entity_decode($text),
        ENT_QUOTES | ENT_COMPAT,
        'UTF-8')));
    } else {
      return '';
    }
  }

  public function getEscapedString($text) {
    return htmlspecialchars(
      $text,
      ENT_QUOTES,
      'UTF-8');
  }

  public function trimText($text, $length) {
    if (strlen($text) > $length) {
      $text = substr($text, 0, $length);
    }
    return $text;
  }

  public function getGuestLogin($session) {
    return (isset($session->data['guest']))
      ? $session->data['guest']
      : null;
  }

  public function getPii($config, $customer, $guest) {
    $facebook_pixel_pii_fae = array();
    if ($config->get(self::FACEBOOK_PIXEL_USE_PII) === 'true') {
      $email = '';
      $firstname = '';
      $lastname = '';
      $telephone = '';

      // use the logged in customer details
      if ($customer->isLogged()) {
        $email = $customer->getEmail();
        $firstname = $customer->getFirstName();
        $lastname = $customer->getLastName();
        $telephone = $customer->getTelephone();
      }

      // use the guest log in details
      if (isset($guest)) {
        $email = (isset($guest['email']))
          ? $guest['email']
          : '';
        $firstname = (isset($guest['firstname']))
          ? $guest['firstname']
          : '';
        $lastname = (isset($guest['lastname']))
          ? $guest['lastname']
          : '';
        $telephone = (isset($guest['telephone']))
          ? $guest['telephone']
          : '';
      }

      $enabled_amm_fields = explode(
        ',', 
        $config->get(FacebookCommonUtils::FACEBOOK_PIXEL_ENABLED_AAM_FIELDS)
      );
      if ($email && in_array('em', $enabled_amm_fields)) {
        $facebook_pixel_pii_fae['em'] =
          $this->getEscapedString($email);
      }
      if ($firstname && in_array('fn', $enabled_amm_fields)) {
        $facebook_pixel_pii_fae['fn'] =
          $this->getEscapedString($firstname);
      }
      if ($lastname && in_array('ln', $enabled_amm_fields)) {
        $facebook_pixel_pii_fae['ln'] =
          $this->getEscapedString($lastname);
      }
      if ($telephone && in_array('ph', $enabled_amm_fields)) {
        $facebook_pixel_pii_fae['ph'] =
          $this->getEscapedString($telephone);
      }
    }
    return $facebook_pixel_pii_fae;
  }

  public function getDAPixelParamsForProducts($params, $event_id = null) {
    $content_ids = array();
    $value = 0;
    $num_items = 0;
    $last_product_name = '';
    foreach ($params->getProducts() as $product) {
      array_push($content_ids, (string)$product['product_id']);
      $price = (isset($product['special']) && (float)$product['special'])
        ? (float)$product['special']
        : (float)$product['price'];
      $purchase_quantity =
        ($params->hasQuantity())
        ? $product['quantity']
        : 1;
      $value = $value + ($price * $purchase_quantity);
      $num_items = $num_items + $purchase_quantity;
      $last_product_name = $product['name'];
    }
    $facebook_pixel_params = array();
    $facebook_pixel_params['event_name'] = $params->getEventName();
    if (sizeof($content_ids)) {
      $facebook_pixel_params['content_type'] = 'product';
    }
    $facebook_pixel_params['content_ids'] = $content_ids;
    $facebook_pixel_params['value'] = $params->getCurrency()->format(
      $value,
      $params->getCurrencyCode(),
      '',
      false);
    $facebook_pixel_params['currency'] = $params->getCurrencyCode();
    $facebook_pixel_params['content_name'] =
      (sizeof($params->getProducts()) == 1)
      ? $this->getProperFormattedString($last_product_name)
      : "";
    $facebook_pixel_params['num_items'] = $num_items;
    if (!empty($event_id)) {
      $facebook_pixel_params['event_id'] = $event_id;
    }
    return $facebook_pixel_params;
  }

  public function doesDefaultCurrencySupportCents(
    $default_currency_code,
    $default_currency) {
    // treat 0 decimal_place as not supporting cents
    return ($default_currency)
      ? ($default_currency['decimal_place']) ? true : false
      : true;
  }

  public function getDAPixelParamsForProductListing($params, $event_id = null) {
    $facebook_pixel_event_params_fae = array(
      'event_name' => $params->getEventName(),
      'num_items' => 0);
    if (sizeof($params->getProducts())) {
      $facebook_pixel_event_params_fae =
        $this->getDAPixelParamsForProducts($params);
    }
    $facebook_pixel_event_params_fae[
      $params->getParamNameUsedInProductListing()] =
      $this->getProperFormattedString(
        $params->getParamValueUsedInProductListing());
    $facebook_pixel_event_params_fae['is_custom_event'] =
      $params->isCustomEvent();
    if (!empty($event_id)) {
      $facebook_pixel_event_params_fae['event_id'] = $event_id;
    }
    return $facebook_pixel_event_params_fae;
  }

  public function lowercaseIfAllCaps($string) {
    // if contains lowercase or non-western characters, don't update string
    if (!preg_match('/[a-z]/', $string)
      && !preg_match('/[^\\p{Common}\\p{Latin}]/u', $string)) {
      $latin_string = preg_replace('/[^\\p{Latin}]/u', '', $string);
      if ($latin_string !== ''
        && mb_strtoupper($latin_string, 'utf-8') === $latin_string) {
        return strtolower($string);
      }
    }
    return $string;
  }

  private function getPluginAgentName() {
    return $this->pluginAgentName;
  }

  public function getLatestPluginVersion() {
    try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, self::FACEBOOK_LATEST_RELEASE_URL);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_USERAGENT, "curl");

      ob_start();
      curl_exec($ch);
      curl_close($ch);
      $lines = ob_get_contents();
      ob_end_clean();
      $json = json_decode($lines, true);

      if (!$json || !isset($json['tag_name'])) {
        return false;
      }

      $version_latest = $json['tag_name'];
      return (substr($version_latest, 0, 1) == 'v')
        ? substr($version_latest, 1)
        : false;
    } catch (Exception $e) {
      $this->faeLog = new Log(self::FAE_LOG_FILENAME);
      $this->faeLog->write($e->getMessage());
    }
  }

  private function loadFacebookModel($model_name, $registry) {
    $model = null;
    // the facebook models have been moved to system/library/model/extension
    require_once
      DIR_APPLICATION . "../system/library/model/" . $model_name . ".php";
    switch ($model_name) {
      case "extension/facebooksetting":
        $model =
          new ModelExtensionFacebookSetting($registry);
        break;
      case "extension/facebookproduct":
        $model =
          new ModelExtensionFacebookProduct($registry);
        break;
    }
    return $model;
  }

  public function loadFacebookSettingsModel($registry) {
    return $this->loadFacebookModel("extension/facebooksetting", $registry);
  }

  public function loadFacebookProductModel($registry) {
    return $this->loadFacebookModel("extension/facebookproduct", $registry);
  }

  public function isTrueFalseString($value) {
    return ($value === 'true' || $value === 'false');
  }

  public function isValidSetting($setting, $value) {
    $is_valid = true;
    switch ($setting) {
      case FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID:
      case FacebookCommonUtils::FACEBOOK_PIXEL_ID:
      case FacebookCommonUtils::FACEBOOK_CATALOG_ID:
      case FacebookCommonUtils::FACEBOOK_PAGE_ID:
      case FacebookCommonUtils::FACEBOOK_FEED_ID:
      case FacebookCommonUtils::FACEBOOK_UPLOAD_ID:
        $is_valid = ctype_digit($value);
        break;

      case FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII:
      case FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR:
      case FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE:
      case FacebookCommonUtils::FACEBOOK_MESSENGER:
      case FacebookCommonUtils::FACEBOOK_USE_S2S:
        $is_valid = $this->isTrueFalseString($value);
        break;
    }

    return $is_valid;
  }

  public function getPixelAAMSetting($pixel_id) {
    $settings = AdsPixelSettings::buildFromPixelId($pixel_id);
    if ($settings !== null) {
      return $settings->getEnableAutomaticMatching() ? 'true' : 'false';
    }
    return 'false';
  }
  
  public function getPixelEnabledAAMFields($pixel_id) {
    $settings = AdsPixelSettings::buildFromPixelId($pixel_id);
    if ($settings !== null) {
      $enabled_aam_fileds = $settings->getEnabledAutomaticMatchingFields();
      return implode(',', $enabled_aam_fileds);
    }
    return '';
  }
}
