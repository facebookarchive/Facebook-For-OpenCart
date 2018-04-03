<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ControllerFacebookFacebookAdsExtension extends Controller {
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
    // perform a system check to ensure plugin is intact
    // by tracking all the error messages
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
    $error_message = $this->getErrorMessageForMissingAssets(
      $missing_database_tables,
      $error_message_title);
    if ($error_message) {
      $all_error_messages[] = $error_message;
    }

    if (sizeof($all_error_messages) > 0) {
      $data = $this->getDataForErrorView($all_error_messages);
      $this->response->setOutput(
        $this->load->view('error/not_found.tpl', $data));
      return;
    }

    $this->facebookcommonutils = new FacebookCommonUtils();
    $this->facebookgraphapi = new FacebookGraphAPI();

    $this->load->language('facebook/facebookadsextension');
    $this->document->setTitle($this->language->get('heading_title'));

    // Run currency update
    if ($this->config->get('config_currency_auto')) {
      $this->load->model('localisation/currency');
      $this->model_localisation_currency->refresh();
    }

    $this->load->model('catalog/product');

    $this->load->model('facebook/facebooksetting');
    $facebook_setting = $this->model_facebook_facebooksetting->getSettings();

    $data = array();
    $data['heading_title'] = $this->language->get('heading_title');
    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link(
      'common/dashboard',
      'token=' . $this->session->data['token'],
      true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link(
      'facebook/facebookadsextension',
      'token=' . $this->session->data['token'],
      true)
    );

    $data['token'] = $this->session->data['token'];
    $data['debug_url'] = (isset($this->request->get['debug_url']))
      ? $this->request->get['debug_url']
      : '';
    $data[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID] =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_DIA_SETTING_ID]
      : '';
    $data[FacebookCommonUtils::FACEBOOK_PIXEL_ID] =
      isset($facebook_setting[FacebookCommonUtils::FACEBOOK_PIXEL_ID])
      ? $facebook_setting[FacebookCommonUtils::FACEBOOK_PIXEL_ID]
      : '';
    $data['base_currency'] = $this->config->get('config_currency');
    $data['store_name'] = $this->config->get('config_name');
    $data['opencart_version'] = VERSION;
    $data['plugin_version'] = $this->facebookcommonutils->getPluginVersion();

    $filter_status = array('filter_status' => 1);
    $data['total_visible_products'] =
      $this->model_catalog_product->getTotalProducts($filter_status);
    $data['sample_feed'] = $this->load->controller(
      'facebook/facebookproduct/getSampleProductFeed');
    if (!$data['sample_feed']) {
      $data['sample_feed'] = '[[]]';
    }

    $data['download_log_link'] = $this->url->link(
      'facebook/facebookadsextension/downloadfaelogfile',
      'token=' . $this->session->data['token'],
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

    $this->response->setOutput(
      $this->load->view('facebook/facebookadsextension.tpl', $data));
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

    $this->faeLog->write('Updating FAE settings - ' .
      implode(',', array_keys($data)));
    $this->load->model('facebook/facebooksetting');
    $this->model_facebook_facebooksetting->updateSettings($data);

    $json = array('success' => 'true');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
    $this->faeLog->write('Complete - Updating FAE settings');
  }

  public function clearAllFacebookProducts() {
    $result = $this->load->controller(
      'facebook/facebookproduct/clearAllFacebookProducts');
    $json = array('success' => 'true');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function syncAllProducts() {
    $result = $this->load->controller(
      'facebook/facebookproduct/syncAllProducts');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }

  public function deleteSettings() {
    $this->faeLog->write('Deleting FAE settings');
    $this->load->model('facebook/facebooksetting');
    $this->model_facebook_facebooksetting->deleteSettings();

    $json = array('success' => 'true');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
    $this->faeLog->write('Complete - Deleting FAE settings');
  }

  public function downloadFAELogFile() {
    $this->load->language('facebook/facebookadsextension');
    $file = DIR_LOGS . FacebookCommonUtils::FAE_LOG_FILENAME;

    if (file_exists($file) && filesize($file) > 0) {
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
        'facebook/facebookadsextension',
        'token=' . $this->session->data['token'],
        true));
    }
  }

  public function syncAllProductsUsingFeed() {
    try {
      $result = $this->load->controller(
        'facebook/facebookproductfeed/syncAllProductsUsingFeed');
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($result));
    } catch (Exception $e) {
      header("HTTP/1.1 400 " . $e->getMessage());
    }
  }

  public function getInitialProductSyncStatus() {
    try {
      $result = $this->load->controller(
        'facebook/facebookproductfeed/getInitialProductSyncStatus');
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($result));
    } catch (Exception $e) {
      header("HTTP/1.1 400 " . $e->getMessage());
    }
  }

  public function isWritableProductFeedFolderAvailable() {
    try {
      $result = $this->load->controller(
        'facebook/facebookproductfeed/isWritableProductFeedFolderAvailable');
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
      DIR_APPLICATION . '/controller/facebook/ControllerFacebookFacebookProductTrait.php',
      DIR_APPLICATION . '/controller/facebook/facebookadsextension.php',
      DIR_APPLICATION . '/controller/facebook/facebookproduct.php',
      DIR_APPLICATION . '/controller/facebook/facebookproductfeed.php',
      DIR_APPLICATION . '/language/en-gb/facebook/facebookadsextension.php',
      DIR_APPLICATION . '/language/english/facebook/facebookadsextension.php',
      DIR_APPLICATION . '/model/facebook/facebookproduct.php',
      DIR_APPLICATION . '/model/facebook/facebooksetting.php',
      DIR_APPLICATION . '/view/images/facebook/background.png',
      DIR_APPLICATION . '/view/images/facebook/buttonbg.png',
      DIR_APPLICATION . '/view/images/facebook/fbicons.png',
      DIR_APPLICATION . '/view/images/facebook/loadingicon.gif',
      DIR_APPLICATION . '/view/javascript/facebook/dia.js',
      DIR_APPLICATION . '/view/stylesheet/facebook/dia.css',
      DIR_APPLICATION . '/view/stylesheet/facebook/feed.css',
      DIR_APPLICATION . '/view/stylesheet/facebook/pixel.css',
      DIR_APPLICATION . '/view/template/facebook/facebookadsextension.tpl',
      DIR_SYSTEM . '/library/facebookcommonutils.php',
      DIR_SYSTEM . '/library/facebookgraphapi.php',
      DIR_SYSTEM . '/library/facebookgraphapierror.php',
      DIR_SYSTEM . '/library/facebookproductapiformatter.php',
      DIR_SYSTEM . '/library/facebookproductfeedformatter.php',
      DIR_SYSTEM . '/library/facebookproductformatter.php',
      DIR_SYSTEM . '/library/facebooksampleproductfeedformatter.php',
      DIR_SYSTEM . '/library/facebooktax.php',
      DIR_CATALOG . '/controller/facebook/facebookproduct.php',
      DIR_CATALOG . '/view/javascript/facebook/facebook_pixel.js',
// system auto generated, DO NOT MODIFY
      null
    );
  }

  private function getFoldersWithMissingFiles() {
    // retrieves all folders + parent folders which contain missing files
    $required_files = $this->getRequiredFiles();
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
          $folder = dirname($required_file, 2);
        }


        // loops through the entire folder tree to detect which
        // folders are not accessible
        do {
          // keeps if this is a first occurence of the folder
          if (!in_array($folder, $folders_with_missing_files)) {
            $folders_with_missing_files[] = $folder;
          }

          if (file_exists($folder)) {
            // breaks out if the folder is accessible, which means
            // that the parent folders have the correct permissions
            break;
          } else {
            // goes up to the parent folder
            $folder =  dirname($folder, 1);
          }
        } while ($folder !== $_SERVER['DOCUMENT_ROOT']);
      });
    return $folders_with_missing_files;
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
      function($required_table) use(&$missing_database_tables) {
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
      function($missing_asset)
        use(&$error_message_for_missing_assets) {
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
    $this->load->language('facebook/facebookadsextension');
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
      'token=' . $this->session->data['token'],
      true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $heading_title,
      'href' => $this->url->link(
      'facebook/facebookadsextension',
      'token=' . $this->session->data['token'],
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
      function($error_message) use(&$data) {
        $data['text_not_found'] = $data['text_not_found'] .
          sprintf('<li>%s</li><br/>',
            $error_message);
      });
    $data['text_not_found'] = $data['text_not_found'] .
      sprintf('</ol><p style="color:red;"><strong>Please uninstall and reinstall ' .
      'the Facebook Ads Extension after correcting the above %d step%s' .
      '.</strong></p>',
      sizeof($error_messages),
      (sizeof($error_messages) > 1 ? 's' : ''));

    return $data;
  }
}
