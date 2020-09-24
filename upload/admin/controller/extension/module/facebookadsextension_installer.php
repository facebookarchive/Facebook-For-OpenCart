<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.
class ControllerExtensionModuleFacebookAdsExtensionInstaller
  extends Controller {

  public function install() {
// system auto generated, DO NOT MODIFY
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

  // adds Facebook Ads Extension access permission for Administrator
  $this->load->model('user/user_group');
  $user_groups = $this->model_user_user_group->getUserGroups();
  $admin_user_group_id = null;
  foreach ($user_groups as $user_group) {
    if ($user_group['name'] === 'Administrator') {
      $admin_user_group_id = $user_group['user_group_id'];
      break;
    }
  }
  if (!is_null($admin_user_group_id)) {
    $this->model_user_user_group->addPermission(
      $admin_user_group_id,
      "access",
      "extension/facebookadsextension");
    $this->model_user_user_group->addPermission(
      $admin_user_group_id,
      "modify",
      "extension/facebookadsextension");
  }

  // creates the facebook_events table
  $facebook_events_table = 'facebook_events';
  $facebook_events_table_exists_sql = sprintf("SHOW TABLES IN `%s` " .
    "LIKE '%s'",
    DB_DATABASE,
    DB_PREFIX.$facebook_events_table);
  $data = $this->db->query($facebook_events_table_exists_sql)->rows;
  // checks if the table exist
  if (sizeof($data) == 0) {
    $create_facebook_events_sql = sprintf('CREATE TABLE `%s`.' .
      '`%s` (' .
      '`id` INT NOT NULL AUTO_INCREMENT, ' .
      '`data` NVARCHAR (4092) NOT NULL, ' .
      '`ts` DATETIME NOT NULL, ' .
      'PRIMARY KEY (`id`))',
      DB_DATABASE,
      DB_PREFIX.$facebook_events_table);
    $this->db->query($create_facebook_events_sql);
  }

  // checks if the xml file is TEXT type (64KB)
  // increases the size of the xml field on the modifications table
  // from TEXT to MEDIUMTEXT
  // this is to overcome the 64KB limitation bug for the install.xml file
  $modifications_xml_column_details_sql = sprintf(
    "SHOW COLUMNS FROM `%s`.%smodification where field = 'xml'",
    DB_DATABASE,
    DB_PREFIX);
  $data = $this->db->query($modifications_xml_column_details_sql)->rows;
  if (sizeof($data) == 1 && isset($data[0]['Type']) && $data[0]['Type'] === 'text') {
    $modify_xml_column_sql = sprintf(
      "ALTER TABLE `%s`.%smodification CHANGE xml xml MEDIUMTEXT " . "
      CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL",
      DB_DATABASE,
      DB_PREFIX);
    $this->db->query($modify_xml_column_sql);
  }

  // delete module folder to prevent path error in lower version
  if (version_compare(VERSION , '2.0.3.1') <= 0) {
    /* As this file lives inside the admin folder, the paths below shouldn't mention "admin" as in OpenCart you can have a custom name for your 
    "admin" folder, so Constant DIR_APPLICATION will give the exact folder, whatever that is.*/
    $directory = DIR_APPLICATION . '/controller/extension/module/';
    unlink($directory . 'facebookadsextension_installer.php');
    // check if the folder is empty before we remove the folder
    $iterator = new \FilesystemIterator($directory);
    if (!$iterator->valid()) {
      rmdir($directory);
    }
  }

    // generates the pixel signature if FAE is setup
  $this->load->controller(
    'extension/facebookadsextension/updatePixelSignature');
// system auto generated, DO NOT MODIFY
  }

  public function index() {
    $template_engine = $this->config->get('template_engine');
    $template_file_extension =
      (isset($template_engine) || $template_engine === 'twig')
        ? ''
        : '.tpl';

    $this->load->language('extension/module/facebookadsextension_installer');
    $this->document->setTitle($this->language->get('heading_title'));

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
        'extension/module/facebookadsextension_installer',
        $data['token_string'],
        true)
    );
    // this is the actual link for the Facebook Ads Extension
    // that we will be linking from this module
    $fae_link = $this->url->link(
        'extension/facebookadsextension',
        $data['token_string'],
        true);

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $data['fae_entry_point_text'] =
      sprintf($this->language->get('fae_entry_point_text'), $fae_link);

    $this->response->setOutput(
      $this->load->view(
        'extension/module/facebookadsextension_installer' . $template_file_extension,
        $data));
  }

  private function getTokenString() {
    return 'user_token=' . $this->getToken() . '&token=' . $this->getToken();
  }

  private function getToken() {
    return (isset($this->session->data['user_token']))
      ? $this->session->data['user_token']
      : $this->session->data['token'];
  }
}
