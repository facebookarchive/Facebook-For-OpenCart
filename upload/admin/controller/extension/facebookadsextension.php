<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

require_once(DIR_APPLICATION.'../system/library/controller/extension/facebookproductfeed.php');

class ControllerExtensionFacebookAdsExtension extends Controller {
  private $faeLog;

  public function __construct($registry) {
    parent::__construct($registry);
    if (class_exists('FacebookCommonUtils')) {
      $this->faeLog = new Log(FacebookCommonUtils::FAE_LOG_FILENAME);
      $this->facebookcommonutils = new FacebookCommonUtils();
    } else {
      error_log("class FacebookCommonUtils does not exist");
    }
    $this->facebook_product_feed_controller = new ControllerExtensionFacebookProductFeed($registry);
  }

  public function index() {
    $template_engine = $this->config->get('template_engine');
    $template_file_extension =
      (isset($template_engine) || $template_engine === 'twig')
        ? ''
        : '.tpl';

    // validates the plugin
    $all_error_messages = $this->validate();

    if (sizeof($all_error_messages) > 0) {
      $data = $this->getDataForErrorView($all_error_messages);
      $this->response->setOutput(
        $this->load->view(
          'error/not_found' . $template_file_extension,
          $data));
      return;
    }

    $this->facebookgraphapi = new FacebookGraphAPI();

    $this->load->language('extension/facebookadsextension');
    $this->document->setTitle($this->language->get('heading_title'));

    // Run currency update
    if ($this->config->get('config_currency_auto')) {
      $this->load->model('localisation/currency');
      if (method_exists($this->model_localisation_currency, 'refresh')) {
        $this->model_localisation_currency->refresh();
      }
    }

    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $facebook_setting = $this->model_extension_facebooksetting
      ->getSettings();

    $data = array();

    $data['token_string'] = $this->getTokenString();

    $data['heading_title'] = $this->language->get('heading_title');
    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link(
        'common/dashboard',
        $data['token_string'],
        true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link(
        'extension/facebookadsextension',
        $data['token_string'],
        true)
    );

    $data['debug_url'] = (isset($this->request->get['debug_url']))
      ? $this->request->get['debug_url']
      : '';

    $data['has_gzip_support'] = extension_loaded('zlib') ? 'true' : 'false';
    $data['time_zone_id'] = date('Z');
    $data['php_version'] = phpversion();

    $data[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID] =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])
        ? $facebook_setting[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID]
        : '';
    $data[FacebookCommonUtils::FACEBOOK_PIXEL_ID] =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_PIXEL_ID])
        ? $facebook_setting[FacebookCommonUtils::FACEBOOK_PIXEL_ID]
        : '';
    $data['base_currency'] =
      strtoupper(addslashes($this->config->get('config_currency')));
    $data['store_name'] = addslashes($this->config->get('config_name'));
    $data['opencart_version'] = VERSION;
    $data['plugin_version'] = $this->facebookcommonutils->getPluginVersion();

    $data['total_visible_products'] =
      $this->facebook_product_feed_controller->getTotalEnabledProducts();

    $data['feed_url'] = HTTP_CATALOG.'index.php?route=extension/facebookfeed/genFeed';
    $data['feed_ping_url'] = HTTP_CATALOG.'index.php?route=extension/facebookfeed/genFeedPing';
    $data['feed_migrated'] = $this->getFeedMigrated();

    $data['sample_feed'] = $this->facebook_product_feed_controller->getSampleProductFeed();
    if (!$data['sample_feed']) {
      $data['sample_feed'] = '[[]]';
    }

    $data['download_log_link'] = $this->url->link(
      'extension/facebookadsextension/downloadfaelogfile',
      $data['token_string'],
      true);

    $data['sub_heading_title'] = $this->language->get('sub_heading_title');
    $data['body_text'] = $this->language->get('body_text');
    $data['button_text'] = ($data[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])
      ? $this->language->get('button_manage_settings')
      : $this->language->get('button_get_started');
    $data['resync_text'] = $this->language->get('resync_text');
    $data['resync_confirm_text'] = $this->language->get('resync_confirm_text');
    $data['download_log_file_text'] =
      $this->language->get('download_log_file_text');
    $data['product_sync_tooltip_text'] =
      $this->language->get('product_sync_tooltip_text');
    $data['sub_heading_settings'] =
      $this->language->get('sub_heading_settings');
    $data['alert_settings_saved'] =
      $this->language->get('alert_settings_saved');

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $data['download_log_file_error_warning'] =
      isset($this->session->data['download_log_file_error_warning'])
        ? $this->session->data['download_log_file_error_warning']
        : '';
    $this->session->data['download_log_file_error_warning'] = '';

    // checking if there is a newer upgrade available
    $data['plugin_upgrade_message'] = ($this->hasNewUpgradeAvailable())
      ? FacebookCommonUtils::PLUGIN_UPGRADE_MESSAGE
      : '';

    // default the cookie bar to be enabled if the setting is not available
    $data['enable_cookie_bar'] =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR]
      : 'true';
    // this will control if the cookie bar checkbox is checked
    $data['checked_enable_cookie_bar'] =
      (strcmp($data['enable_cookie_bar'], 'true') === 0)
      ? 'checked'
      : '';
    $data['enable_cookie_bar_text'] =
      $this->language->get('enable_cookie_bar_text');
    $data['enable_cookie_bar_key'] =
      FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR;

    // performs a diagnostics check on the pixel and customer chat for the store front
    // and gets any error messages from the check
    $data['plugin_code_injection_error_messages'] =
      $this->validatePluginCodeInjection();

    // display warning only if user has migrated and set webstore to maintenance mode
    $data['plugin_feed_migrated_and_website_in_maintenance_message'] =
      $this->isFeedMigratedAndWebsiteInMaintenance()
      ? FacebookCommonUtils::FACEBOOK_FEED_MIGRATED_AND_WEBSITE_IN_MAINTENANCE_MESSAGE
      : '';

    // default the special price to be enabled if the setting is not available
    $data['enable_special_price'] =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE]
      : 'true';
    // this will control if the enable special price checkbox is checked
    $data['checked_enable_special_price'] =
      (strcmp($data['enable_special_price'], 'true') === 0)
      ? 'checked'
      : '';
    $data['enable_special_price_text'] =
      $this->language->get('enable_special_price_text');
    $data['enable_special_price_key'] =
      FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE;
    
    $data['opencart_facebook_app_id'] = FacebookCommonUtils::OPENCART_FACEBOOK_APP_ID;
    $data['external_business_id'] = HTTP_CATALOG;
    $data['opencart_server_base_url'] = FacebookCommonUtils::OPENCART_SERVER_BASE_URL;
    $data['opencart_iframe_url'] = $data['opencart_server_base_url'] . FacebookCommonUtils::OPENCART_FBE_IFRAME_PATH . '?' 
    . 'external_business_id=' . urlencode($data['external_business_id'])
    . '&business_name=' . $data['store_name']
    . '&feed_url=' . urlencode($data['feed_url'])
    . '&feed_ping_url=' . urlencode($data['feed_ping_url'])
    . '&timezone=' . date_default_timezone_get()
    . '&currency=' . $data['base_currency'];
    $data[FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN] =
    isset($facebook_setting[FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN]
      : '';
    $data[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED] = 
    isset($facebook_setting[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED]
      : false;

    if(!empty($data[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])){
      $data['opencart_iframe_url'] = $data['opencart_iframe_url'] 
      .'&merchant_settings_id=' . $data[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID];
    }

    if(!empty($data[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED])){
      $data['opencart_iframe_url'] = $data['opencart_iframe_url'] 
      .'&fbe_v2_installed=' . $data[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED];
    }

    $this->response->setOutput(
      $this->load->view(
        'extension/facebookadsextension' . $template_file_extension,
        $data));
  }

  public function isFeedMigratedAndWebsiteInMaintenance() {
    $facebook_settings = $this->model_extension_facebooksetting->getSettings();
    return $facebook_settings
      && isset($facebook_settings[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])
      && $facebook_settings[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID]
      && $this->getFeedMigrated()
      && $this->config->get('config_maintenance');
  }

  public function showFeedMigrationWarningMessage() {
    $facebook_settings = $this->model_extension_facebooksetting->getSettings();
    return $facebook_settings
      && isset($facebook_settings[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID]) 
      && $facebook_settings[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID]
      && !$this->getFeedMigrated();
  }

  private function getFeedMigrated() {
    $facebook_settings = $this->model_extension_facebooksetting->getSettings();
    return $facebook_settings
      && isset($facebook_settings[FacebookCommonUtils::FACEBOOK_FEED_MIGRATED]) 
      && $facebook_settings[FacebookCommonUtils::FACEBOOK_FEED_MIGRATED];
  }

  private function validatePluginCodeInjection() {
    // validate that the plugin injected the codes successfully
    $error_messages = array();
    array_push($error_messages, $this->validateWebStoreCodeInjection());
    return implode(',', $error_messages);
  }

  private function validateWebStoreCodeInjection() {
    $error_sections = array();

    // read the html page of the web store
    $curl = curl_init(HTTPS_CATALOG);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);

    // always do the check for pixel
    // added in a second check for the codeline with no spaces
    // this is to cater for websites that have optimizers that strip away empty spaces
    if (strpos($result, FacebookCommonUtils::FACEBOOK_PIXEL_CODE_INDICATOR) == 0) {
      array_push($error_sections, 'Facebook pixel');
    }

    // only do the check for messenger chat if it is enabled
    // added in a second check for the codeline with no spaces
    // this is to cater for websites that have optimizers that strip away empty spaces
    if (strpos($result, FacebookCommonUtils::FACEBOOK_MESSENGER_CHAT_CODE_INDICATOR) == 0
      && $this->config->get(FacebookCommonUtils::FACEBOOK_MESSENGER) === 'true') {
      array_push($error_sections, 'Messenger customer chat');
    }

    return (sizeof($error_sections))
      ? sprintf(FacebookCommonUtils::MISSING_WEB_STORE_CODE_ERROR_MESSAGE, implode(', ', $error_sections))
      : '';
  }

  private function logVersionsTologFile() {
    $this->faeLog->write('Facebook Ads Extension version = ' .
      $this->facebookcommonutils->getPluginVersion());
    $this->faeLog->write('OpenCart version = ' . VERSION);
    $this->faeLog->write('PHP version = ' . PHP_VERSION);
  }

  private function logFAESettingsAvailability() {
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $facebook_setting = $this->model_extension_facebooksetting
      ->getSettings();
    $this->faeLog->write(
      'Verifying Availability of Facebook Ads Extension Settings Availability');
    $this->faeLog->write('Pixel ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_PIXEL_ID));
    $this->faeLog->write('Pixel use PII = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII));
    $this->faeLog->write('Page ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_PAGE_ID));
    $this->faeLog->write('System User Access Token = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN));
    $this->faeLog->write('Messenger plugin = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_MESSENGER));
  }

  private function isFAESettingAvailableAsString(
    $facebook_setting,
    $fae_setting) {
    return (isset($facebook_setting[$fae_setting])) ? 'true' : 'false';
  }

  public function validate() {
    $php_version_error = $this->validatePHPVersion();
    if (sizeof($php_version_error)) {
      return $php_version_error;
    }

    return $this->validateMissingFilesAndTables();
  }

  private function validatePHPVersion() {
    // our plugin supports from PHP 5.4 onwards, which is in sync with OpenCart
    // http://docs.opencart.com/en-gb/requirements/
    return (version_compare(PHP_VERSION, '5.4.0') >= 0)
      ? array()
      : array('Facebook Ads Extension supports only for PHP 5.4 or above. ' .
        'Your PHP version is currently ' . PHP_VERSION . '. ' .
        'Please upgrade your PHP to 5.4 or above.');
  }

  public function validateMissingFilesAndTables() {
    // performing a system check to ensure plugin is intact
    // by tracking all the required files and database tables
    $all_error_messages = array();

    // 1. to ensure all required files are copied over to the webfolder
    $folders_with_missing_files = $this->getFoldersWithMissingFiles();
    $error_message_title = 'We have detected missing files for ' .
      'Facebook Ads Extension. ' .
      'Please enable read, write and execute access permissions ' .
      'for these files and folders.' .
      '<br/>';
    $error_message = $this->getErrorMessageForMissingAssets(
      $folders_with_missing_files,
      $error_message_title);
    if ($error_message) {
      $all_error_messages[] = $error_message;
    }

    return $all_error_messages;
  }

  private function getToken() {
    return (isset($this->session->data['user_token']))
      ? $this->session->data['user_token']
      : $this->session->data['token'];
  }

  private function getTokenString() {
    return 'user_token=' . $this->getToken() . '&token=' . $this->getToken();
  }

  public function updateSettings() {
    $data = array();

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_ID])
      && $this->facebookcommonutils->isValidSetting(
        FacebookCommonUtils::FACEBOOK_PIXEL_ID,
        $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_ID])) {
      $data[FacebookCommonUtils::FACEBOOK_PIXEL_ID] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_ID];
    }

    if (isset(
      $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII])
      && $this->facebookcommonutils->isValidSetting(
        FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII,
        $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII])) {
      $data[FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_PAGE_ID])
      && $this->facebookcommonutils->isValidSetting(
        FacebookCommonUtils::FACEBOOK_PAGE_ID,
        $this->request->post[FacebookCommonUtils::FACEBOOK_PAGE_ID])) {
      $data[FacebookCommonUtils::FACEBOOK_PAGE_ID] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_PAGE_ID];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN])) {
      $data[FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED])) {
      $data[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_FBE_V2_INSTALLED];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_MESSENGER])
      && $this->facebookcommonutils->isValidSetting(
        FacebookCommonUtils::FACEBOOK_MESSENGER,
        $this->request->post[FacebookCommonUtils::FACEBOOK_MESSENGER])) {
      $data[FacebookCommonUtils::FACEBOOK_MESSENGER] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_MESSENGER];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR])
      && $this->facebookcommonutils->isValidSetting(
        FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR,
        $this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR])) {
      $data[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE])
      && $this->facebookcommonutils->isValidSetting(
        FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE,
        $this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE])) {
      $data[FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_SPECIAL_PRICE];
    }

    $this->faeLog->write('Updating FAE settings - ' .
      implode(',', array_keys($data)));
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $this->model_extension_facebooksetting->updateSettings($data);

    $json = array('success' => 'true');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
    $this->faeLog->write('Complete - Updating FAE settings');
  }

  public function deleteSettings() {
    $this->faeLog->write('Deleting FAE settings');
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $this->model_extension_facebooksetting->deleteSettings();

    $json = array('success' => 'true');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
    $this->faeLog->write('Complete - Deleting FAE settings');
  }

  public function downloadFAELogFile() {
    $this->load->language('extension/facebookadsextension');
    $file = DIR_LOGS . FacebookCommonUtils::FAE_LOG_FILENAME;

    if (file_exists($file) && filesize($file) > 0) {
      // logs the FAE, opencart and php versions for debugging
      $this->logVersionsTologFile();

      // logs the availability of each FAE settings for debugging
      $this->logFAESettingsAvailability();

      $this->response->addheader('Pragma: public');
      $this->response->addheader('Expires: 0');
      $this->response->addheader('Content-Description: File Transfer');
      $this->response->addheader('Content-Type: application/octet-stream');
      $this->response->addheader('Content-Disposition: attachment; ' .
        'filename="' . $this->config->get('config_name') .
        '_' . date('Y-m-d_H-i-s', time()) .
        '_' . FacebookCommonUtils::FAE_LOG_FILENAME . '"');
      $this->response->addheader('Content-Transfer-Encoding: binary');

      $this->response->setOutput(
        file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
    } else {
      $this->session->data['download_log_file_error_warning'] =
        sprintf(
          $this->language->get('download_log_file_error_warning'),
          basename($file),
          '0B');
      $this->response->redirect($this->url->link(
        'extension/facebookadsextension',
        $this->getTokenString(),
        true));
    }
  }

  public function isWritableProductFeedFolderAvailable() {
    try {
      $result = $this->facebook_product_feed_controller->isWritableProductFeedFolderAvailable();
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode(array('available' => $result)));
    } catch (Exception $e) {
      header("HTTP/1.1 400 " . $e->getMessage());
    }
  }

  private function getRequiredFiles() {
    // holds all the files that we have added to this OpenCart FAE
    // these are not the core opencart files modified through install.xml
    return array(
// system auto generated, DO NOT MODIFY
      DIR_APPLICATION . '/../admin/controller/extension/facebookadsextension.php',
      DIR_APPLICATION . '/../admin/controller/extension/facebookproduct.php',
      DIR_APPLICATION . '/../admin/controller/extension/facebookproductfeed.php',
      DIR_APPLICATION . '/../admin/controller/extension/facebookproducttrait.php',
      DIR_APPLICATION . '/../admin/controller/extension/module/facebookadsextension_installer.php',
      DIR_APPLICATION . '/../admin/language/en-gb/extension/facebookadsextension.php',
      DIR_APPLICATION . '/../admin/language/en-gb/extension/module/facebookadsextension_installer.php',
      DIR_APPLICATION . '/../admin/language/english/extension/facebookadsextension.php',
      DIR_APPLICATION . '/../admin/language/english/extension/module/facebookadsextension_installer.php',
      DIR_APPLICATION . '/../admin/view/image/facebook/background.png',
      DIR_APPLICATION . '/../admin/view/image/facebook/buttonbg.png',
      DIR_APPLICATION . '/../admin/view/image/facebook/fbicons.png',
      DIR_APPLICATION . '/../admin/view/image/facebook/loadingicon.gif',
      DIR_APPLICATION . '/../admin/view/stylesheet/facebook/dia.css',
      DIR_APPLICATION . '/../admin/view/stylesheet/facebook/feed.css',
      DIR_APPLICATION . '/../admin/view/stylesheet/facebook/pixel.css',
      DIR_APPLICATION . '/../admin/view/template/extension/facebookadsextension.tpl',
      DIR_APPLICATION . '/../admin/view/template/extension/facebookadsextension.twig',
      DIR_APPLICATION . '/../admin/view/template/extension/module/facebookadsextension_installer.tpl',
      DIR_APPLICATION . '/../admin/view/template/extension/module/facebookadsextension_installer.twig',
      DIR_APPLICATION . '/../system/library/controller/extension/facebookproductfeed.php',
      DIR_APPLICATION . '/../system/library/facebookcommonutils.php',
      DIR_APPLICATION . '/../system/library/facebookgraphapi.php',
      DIR_APPLICATION . '/../system/library/facebookgraphapierror.php',
      DIR_APPLICATION . '/../system/library/facebookproductfeedformatter.php',
      DIR_APPLICATION . '/../system/library/facebookproductformatter.php',
      DIR_APPLICATION . '/../system/library/facebookproducttrait.php',
      DIR_APPLICATION . '/../system/library/facebooksampleproductfeedformatter.php',
      DIR_APPLICATION . '/../system/library/facebooktax.php',
      DIR_APPLICATION . '/../system/library/model/extension/facebookproduct.php',
      DIR_APPLICATION . '/../system/library/model/extension/facebooksetting.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookeventparameters.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookfeed.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookpageshopcheckoutredirect.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookproduct.php',
      DIR_APPLICATION . '/../catalog/view/javascript/facebook/cookieconsent.min.js',
      DIR_APPLICATION . '/../catalog/view/javascript/facebook/facebook_pixel_3_0_0.js',
      DIR_APPLICATION . '/../catalog/view/theme/css/facebook/cookieconsent.min.css',
// system auto generated, DO NOT MODIFY
      null
    );
  }

  private function getOmittedFiles($version) {
    // get omitted folder check in different version to allow backward compatibility
    $version_dir = array(
      '2.0.3.1' => array(
        DIR_APPLICATION . '/../admin/controller/extension/module/facebookadsextension_installer.php'
      )
    );

    if (isset($version_dir[$version])) return $version_dir[$version];
    else return array();
  }

  private function getFoldersWithMissingFiles() {
    // retrieves all folders + parent folders which contain missing files
    $required_files = $this->getRequiredFiles();

    // get omitted files for backward compatibility
    if (version_compare(VERSION, '2.0.3.1') <= 0) {
      $omitted_files = $this->getOmittedFiles('2.0.3.1');
      $required_files = array_diff($required_files, $omitted_files);
    }

    $folders_with_missing_files = array();
    array_walk(
      $required_files,
      function($required_file) use(&$folders_with_missing_files) {
        if (!$required_file) {
          return;
        }
        // checks if the file exists on the webserver
        // and returns is file exist
        if (is_file($required_file)) {
          return;
        }

        // reminder of code is finding the folder which has
        // insufficient permissions (rwx)
        $folder = dirname($required_file);
        // checks if the last folder is facebook and
        // and exclude facebook away as we want to get
        // the opencart core folder
        if (substr($folder, -8) === 'facebook') {
          $folder = $this->dirnameRecursive($required_file, 2);
        }

        // loops through the entire folder tree to detect which
        // folders are not accessible
        do {
          // keeps if this is a first occurrence of the folder
          if (!in_array($folder, $folders_with_missing_files)) {
            $folders_with_missing_files[] = realpath($folder);
          }

          if (file_exists($folder)) {
            // breaks out if the folder is accessible, which means
            // that the parent folders have the correct permissions
            break;
          } else {
            // goes up to the parent folder
            $folder = $this->dirnameRecursive($folder, 1);
          }
        } while ($folder !== $_SERVER['DOCUMENT_ROOT']);
      });
    return $folders_with_missing_files;
  }

  private function dirnameRecursive($path, $count = 1) {
    // avoid getting PHP warning for version < 7.
    // this is a trivial backward compatibility fix and can be replace by dirname in the future
    if ($count > 1) {
      return dirname($this->dirnameRecursive($path, --$count));
    } else {
      return dirname($path);
    }
  }

  private function getErrorMessageForMissingAssets(
    $missing_assets,
    $error_message_title) {
    if (sizeof($missing_assets) == 0) {
      return null;
    }

    // prepares error message for the missing assets
    $error_message_for_missing_assets = $error_message_title;
    array_walk(
      $missing_assets,
      function ($missing_asset)
      use (&$error_message_for_missing_assets) {
        $error_message_for_missing_assets =
          $error_message_for_missing_assets .
          $missing_asset .
          '<br/>';
      });
    return $error_message_for_missing_assets;
  }

  private function getDataForErrorView($error_messages) {
    // trying to load from language and default to fixed string
    // if we cant find the header
    $this->load->language('extension/facebookadsextension');
    $this->document->setTitle($this->language->get('heading_title'));
    $heading_title = ($this->language->get('heading_title') == 'heading_title')
      ? $this->language->get('heading_title')
      : 'Facebook Ads Extension';

    $data = array();
    $data['heading_title'] = $heading_title;
    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link(
        'common/dashboard',
        $this->getTokenString(),
        true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $heading_title,
      'href' => $this->url->link(
        'extension/facebookadsextension',
        $this->getTokenString(),
        true)
    );
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    // construct the error message
    $data['text_not_found'] = '<p style="color:red;"><strong>'.
      'There are problems with the installation ' .
      'of the Facebook Ads Extension.</strong><p/><br/>' .
      '<ol>';
    array_walk(
      $error_messages,
      function($error_message) use (&$data) {
        $data['text_not_found'] = $data['text_not_found'] .
          sprintf('<li>%s</li><br/>',
            $error_message);
      });
    $data['text_not_found'] = $data['text_not_found'] .
      sprintf('</ol><p style="color:red;"><strong>Please uninstall and reinstall ' .
        'the Facebook Ads Extension after correcting the above %d step%s<br/>' .
        'Click <a href="https://github.com/facebookincubator/Facebook-For-OpenCart/blob/master/INSTALL_GUIDE.md" target="_blank">here</a> for details on the plugin installation.</strong></p>',
        sizeof($error_messages),
        (sizeof($error_messages) > 1 ? 's' : ''));

    return $data;
  }

  public function hasNewUpgradeAvailable() {
    // determines if we should do the check for new upgrades
    if (!$this->shouldCheckForNewUpgrades()) {
      return false;
    }

    // determines if the current plugin installed is most updated
    // if so, we will update the last check time
    if ($this->isCurrentVersionMostUpdated()) {
      $this->updateLastUpgradeCheckTimeToCurrentDate();
      return false;
    }

    // if we need to check for new upgrades
    // and there is a newer version of the plugin, we will return true
    // the last check time will also NOT be updated
    return true;
  }

  private function getLastUpgradeCheckTime() {
    // gets the last upgrade check time from database settings
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $last_upgrade_check_time = $this->model_extension_facebooksetting
      ->getSetting(FacebookCommonUtils::FACEBOOK_LAST_UPGRADE_CHECK_TIME);
    if (!$last_upgrade_check_time) {
      // the last upgrade check time is not available
      // we are going to manually insert in a setting and put it as
      // a very old date, eg 1970-01-01
      $last_upgrade_check_time = '1970-01-01';
      $data = array(
        FacebookCommonUtils::FACEBOOK_LAST_UPGRADE_CHECK_TIME
          => $last_upgrade_check_time);
      $this->model_extension_facebooksetting->updateSettings($data);
    }
    return $last_upgrade_check_time;
  }

  private function shouldCheckForNewUpgrades() {
    $last_upgrade_check_time = $this->getLastUpgradeCheckTime();
    // we are going to do the check for new upgrades only if
    // current date > last check time
    return (strcmp(date("Y-m-d"), $last_upgrade_check_time) > 0);
  }

  private function isCurrentVersionMostUpdated() {
    // gets the latest version of the plugin
    $latest_version = $this->facebookcommonutils->getLatestPluginVersion();
    return (version_compare(
      $this->facebookcommonutils->getPluginVersion(),
      $latest_version) >= 0);
  }

  private function updateLastUpgradeCheckTimeToCurrentDate() {
    $this->model_extension_facebooksetting =
      $this->facebookcommonutils->loadFacebookSettingsModel($this->registry);
    $data = array(
      FacebookCommonUtils::FACEBOOK_LAST_UPGRADE_CHECK_TIME
        => date("Y-m-d"));
    $this->model_extension_facebooksetting->updateSettings($data);
  }
}
