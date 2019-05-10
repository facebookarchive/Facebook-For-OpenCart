<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ControllerExtensionFacebookAdsExtension extends Controller {
  private $faeLog;

  public function __construct($registry) {
    parent::__construct($registry);
    if (class_exists('FacebookCommonUtils')) {
      $this->faeLog = new Log(FacebookCommonUtils::FAE_LOG_FILENAME);
    } else {
      error_log("class FacebookCommonUtils does not exist");
    }
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

    $this->facebookcommonutils = new FacebookCommonUtils();
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

    $this->load->model('extension/facebooksetting');
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
    $data['base_currency'] = addslashes($this->config->get('config_currency'));
    $data['store_name'] = addslashes($this->config->get('config_name'));
    $data['opencart_version'] = VERSION;
    $data['plugin_version'] = $this->facebookcommonutils->getPluginVersion();

    $data['total_visible_products'] = $this->load->controller(
      'extension/facebookproduct/getTotalEnabledProducts');

    $data['sample_feed'] = $this->load->controller(
      'extension/facebookproduct/getSampleProductFeed');
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
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $data['download_log_file_error_warning'] =
      isset($this->session->data['download_log_file_error_warning'])
        ? $this->session->data['download_log_file_error_warning']
        : '';
    $this->session->data['download_log_file_error_warning'] = '';

    // checking if there is a facebook upload id
    // to decide if the initial product sync has taken place
    $data['initial_product_sync'] =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_UPLOAD_ID])
        ? 'true'
        : 'false';

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

    $this->response->setOutput(
      $this->load->view(
        'extension/facebookadsextension' . $template_file_extension,
        $data));
  }

  private function logVersionsTologFile() {
    $this->facebookcommonutils = new FacebookCommonUtils();
    $this->faeLog->write('Facebook Ads Extension version = ' .
      $this->facebookcommonutils->getPluginVersion());
    $this->faeLog->write('OpenCart version = ' . VERSION);
    $this->faeLog->write('PHP version = ' . PHP_VERSION);
  }

  private function logFAESettingsAvailability() {
    $this->load->model('extension/facebooksetting');
    $facebook_setting = $this->model_extension_facebooksetting
      ->getSettings();
    $this->faeLog->write(
      'Verifying Availability of Facebook Ads Extension Settings Availability');
    $this->faeLog->write('FAE Setting ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID));
    $this->faeLog->write('Pixel ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_PIXEL_ID));
    $this->faeLog->write('Pixel use PII = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII));
    $this->faeLog->write('Catalog ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_CATALOG_ID));
    $this->faeLog->write('Page ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_PAGE_ID));
    $this->faeLog->write('Page Token = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_PAGE_TOKEN));
    $this->faeLog->write('Feed ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_FEED_ID));
    $this->faeLog->write('Upload ID = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_UPLOAD_ID));
    $this->faeLog->write('Upload end time = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_UPLOAD_END_TIME));
    $this->faeLog->write('Messenger plugin = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_MESSENGER));
    $this->faeLog->write('Messenger plugin, greeting text code customization = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_CUSTOMIZATION_GREETING_TEXT_CODE));
    $this->faeLog->write('Messenger plugin, locale customization = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_CUSTOMIZATION_LOCALE));
    $this->faeLog->write('Messenger plugin, theme color code customization = ' .
      $this->isFAESettingAvailableAsString(
        $facebook_setting,
        FacebookCommonUtils::FACEBOOK_CUSTOMIZATION_THEME_COLOR_CODE));
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

    // 2. to ensure the required database tables are created
    $missing_database_tables = $this->getMissingDatabaseTables();
    $error_message_title = 'We have detected these missing database tables ' .
      'for Facebook Ads Extension. ' .
      'Please give the CREATE privilege for your database user.' .
      '<br/>';
    if (version_compare(VERSION, '3.0.0.0') >= 0) {
      // provides an additional message to inform user
      // to install the module for Opencart v3
      $error_message_title = $error_message_title .
        'Please also ensure Facebook Ads Extension module is installed. ' .
        'Access Extensions -> Select Modules from dropdown list -> ' .
        'Click on green install button for Facebook Ads Extension' .
        '<br/><br/>';
    }
    $error_message = $this->getErrorMessageForMissingAssets(
      $missing_database_tables,
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

    if (isset(
      $this->request->post[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])) {
      $data[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_ID])) {
      $data[FacebookCommonUtils::FACEBOOK_PIXEL_ID] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_ID];
    }

    if (isset(
      $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII])) {
      $data[FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_CATALOG_ID])) {
      $data[FacebookCommonUtils::FACEBOOK_CATALOG_ID] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_CATALOG_ID];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_PAGE_ID])) {
      $data[FacebookCommonUtils::FACEBOOK_PAGE_ID] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_PAGE_ID];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_PAGE_TOKEN])) {
      $data[FacebookCommonUtils::FACEBOOK_PAGE_TOKEN] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_PAGE_TOKEN];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_MESSENGER])) {
      $data[FacebookCommonUtils::FACEBOOK_MESSENGER] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_MESSENGER];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_JSSDK_VER])) {
      $data[FacebookCommonUtils::FACEBOOK_JSSDK_VER] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_JSSDK_VER];
    }

    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR])) {
      $data[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR] =
        $this->request->post[FacebookCommonUtils::FACEBOOK_ENABLE_COOKIE_BAR];
    }

    // handle the customizations from messenger chat plugin
    if (isset($this->request->post[FacebookCommonUtils::FACEBOOK_CUSTOMIZATION])) {
      $customization = $this->request->post[FacebookCommonUtils::FACEBOOK_CUSTOMIZATION];
      if (isset($customization['greetingTextCode'])) {
        $data[FacebookCommonUtils::FACEBOOK_CUSTOMIZATION_GREETING_TEXT_CODE] =
          $customization['greetingTextCode'];
      }
      if (isset($customization['locale'])) {
        $data[FacebookCommonUtils::FACEBOOK_CUSTOMIZATION_LOCALE] =
          $customization['locale'];
      }
      if (isset($customization['themeColorCode'])) {
        $data[FacebookCommonUtils::FACEBOOK_CUSTOMIZATION_THEME_COLOR_CODE] =
          $customization['themeColorCode'];
      }
    }

    $this->faeLog->write('Updating FAE settings - ' .
      implode(',', array_keys($data)));
    $this->load->model('extension/facebooksetting');
    $this->model_extension_facebooksetting->updateSettings($data);

    $json = array('success' => 'true');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
    $this->faeLog->write('Complete - Updating FAE settings');
  }

  public function clearAllFacebookProducts() {
    $result = $this->load->controller(
      'extension/facebookproduct/clearAllFacebookProducts');
    $json = array('success' => 'true');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function syncAllProducts() {
    $result = $this->load->controller(
      'extension/facebookproduct/syncAllProducts');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }

  public function deleteSettings() {
    $this->faeLog->write('Deleting FAE settings');
    $this->load->model('extension/facebooksetting');
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

  public function syncAllProductsUsingFeed() {
    try {
      $result = $this->load->controller(
        'extension/facebookproductfeed/syncAllProductsUsingFeed');
      if ($result['success'] !== 'true') {
        // checks if the total number of products is within threshold
        $total = $this->load->controller(
          'extension/facebookproduct/getTotalEnabledProducts');
        $this->faeLog->write('Total number of products = ' . $total);
        if ($total <=
          FacebookCommonUtils::FACEBOOK_THRESHOLD_FOR_INITIAL_SYNC_BY_API) {
          // initial sync using feed fail
          // fallback to use API to initial sync all the products
          // this will result in significantly more time for
          // merchants with many products, eg > 500
          $this->faeLog->write('Syncing using API as fallback');

          $this->updateSettingsToIndicateInitialSyncByAPI();
          $api_result = $this->load->controller(
            'extension/facebookproduct/syncAllProducts');
          $result =
            ($api_result['total_count'] === $api_result['success_count']);
          $this->faeLog->write('Complete Syncing using API as fallback' .
            ' Total=' . $api_result['total_count'] .
            ' Success=' . $api_result['success_count']);
        }
      }

      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($result));
    } catch (Exception $e) {
      header("HTTP/1.1 400 " . $e->getMessage());
    }
  }

  private function updateSettingsToIndicateInitialSyncByAPI() {
    $this->faeLog->write('Updating FAE settings - ');
    $this->load->model('extension/facebooksetting');
    $data = array();
    $data[FacebookCommonUtils::FACEBOOK_FEED_ID] =
      'USING_API_FOR_INITIAL_SYNC';
    $data[FacebookCommonUtils::FACEBOOK_UPLOAD_ID] =
      'USING_API_FOR_INITIAL_SYNC';
    $data[FacebookCommonUtils::FACEBOOK_UPLOAD_END_TIME] =
      'USING_API_FOR_INITIAL_SYNC';
    $this->model_extension_facebooksetting->updateSettings($data);
  }

  public function getInitialProductSyncStatus() {
    try {
      $result = $this->load->controller(
        'extension/facebookproductfeed/getInitialProductSyncStatus');
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($result));
    } catch (Exception $e) {
      $error_message = $e->getMessage();
      $error_code = ($e->getCode() != null)
        ? $e->getCode()
        : 400;
      // special handling of error and Bad request as invalid access token
      if (strtolower($e->getMessage()) === 'error'
        || strtolower($e->getMessage()) === 'bad request') {
        $error_message = sprintf(
          FacebookCommonUtils::ACCESS_TOKEN_INVALID_EXCEPTION_MESSAGE,
          $e->getMessage());
      }
      $this->faeLog->write(
        'Error with getting the initial product sync status '
        . $e->getMessage());
      header("HTTP/1.1 " . $error_code . " " . $error_message);
    }
  }

  public function isWritableProductFeedFolderAvailable() {
    try {
      $result = $this->load->controller(
        'extension/facebookproductfeed/isWritableProductFeedFolderAvailable');
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
      DIR_APPLICATION . '/../admin/model/extension/facebookproduct.php',
      DIR_APPLICATION . '/../admin/model/extension/facebooksetting.php',
      DIR_APPLICATION . '/../admin/view/image/facebook/background.png',
      DIR_APPLICATION . '/../admin/view/image/facebook/buttonbg.png',
      DIR_APPLICATION . '/../admin/view/image/facebook/fbicons.png',
      DIR_APPLICATION . '/../admin/view/image/facebook/loadingicon.gif',
      DIR_APPLICATION . '/../admin/view/javascript/facebook/dia.js',
      DIR_APPLICATION . '/../admin/view/stylesheet/facebook/dia.css',
      DIR_APPLICATION . '/../admin/view/stylesheet/facebook/feed.css',
      DIR_APPLICATION . '/../admin/view/stylesheet/facebook/pixel.css',
      DIR_APPLICATION . '/../admin/view/template/extension/facebookadsextension.tpl',
      DIR_APPLICATION . '/../admin/view/template/extension/facebookadsextension.twig',
      DIR_APPLICATION . '/../admin/view/template/extension/module/facebookadsextension_installer.tpl',
      DIR_APPLICATION . '/../admin/view/template/extension/module/facebookadsextension_installer.twig',
      DIR_APPLICATION . '/../system/library/facebookcommonutils.php',
      DIR_APPLICATION . '/../system/library/facebookgraphapi.php',
      DIR_APPLICATION . '/../system/library/facebookgraphapierror.php',
      DIR_APPLICATION . '/../system/library/facebookproductapiformatter.php',
      DIR_APPLICATION . '/../system/library/facebookproductfeedformatter.php',
      DIR_APPLICATION . '/../system/library/facebookproductformatter.php',
      DIR_APPLICATION . '/../system/library/facebooksampleproductfeedformatter.php',
      DIR_APPLICATION . '/../system/library/facebooktax.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookeventparameters.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookpageshopcheckoutredirect.php',
      DIR_APPLICATION . '/../catalog/controller/extension/facebookproduct.php',
      DIR_APPLICATION . '/../catalog/view/javascript/facebook/cookieconsent.min.js',
      DIR_APPLICATION . '/../catalog/view/javascript/facebook/facebook_pixel.js',
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

  private function getRequiredDatabaseTables() {
    // holds all the new tables which we added to the opencart system
    return array("facebook_product");
  }

  private function getMissingDatabaseTables() {
    // retrieves all missing database tables
    $required_tables = $this->getRequiredDatabaseTables();
    $missing_database_tables = array();
    array_walk(
      $required_tables,
      function($required_table) use (&$missing_database_tables) {
        if (!$required_table) {
          return;
        }

        // checks if the table exist on the database
        $check_table_exist_sql =
          sprintf("SHOW TABLES IN `%s` LIKE '%sfacebook_product'",
            DB_DATABASE,
            DB_PREFIX);
        $data = $this->db->query($check_table_exist_sql)->rows;
        if (sizeof($data) == 0) {
          $missing_database_tables[] = DB_PREFIX . $required_table;
        }
      });
    return $missing_database_tables;
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
    $this->load->model('extension/facebooksetting');
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
    $data =
    $this->load->model('extension/facebooksetting');
    $data = array(
      FacebookCommonUtils::FACEBOOK_LAST_UPGRADE_CHECK_TIME
        => date("Y-m-d"));
    $this->model_extension_facebooksetting->updateSettings($data);
  }
}
