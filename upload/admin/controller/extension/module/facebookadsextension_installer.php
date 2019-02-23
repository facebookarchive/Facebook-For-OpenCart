<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.
class ControllerExtensionModuleFacebookAdsExtensionInstaller
  extends Controller {

  public function install() {
    // system auto generated, DO NOT MODIFY
  // creates the facebook_product table
  $facebook_product_table_exists_sql = sprintf("SHOW TABLES IN `%s` " .
    "LIKE '%sfacebook_product'",
    DB_DATABASE,
    DB_PREFIX);
  $data = $this->db->query($facebook_product_table_exists_sql)->rows;
  // checks if the table exist
  if (sizeof($data) == 0) {
    $create_facebook_product_sql = sprintf("CREATE TABLE `%s`." .
      "`%sfacebook_product` (" .
      "`product_id` INT NOT NULL, " .
      "`facebook_product_id` VARCHAR(20) NOT NULL, " .
      "PRIMARY KEY (`product_id`));",
      DB_DATABASE,
      DB_PREFIX);
    $this->db->query($create_facebook_product_sql);
  }

  // checks if product group id exists
  $facebook_product_group_col_exists_sql = sprintf("SHOW COLUMNS IN " .
    "`%s`.`%sfacebook_product` LIKE 'facebook_product_group_id'",
    DB_DATABASE,
    DB_PREFIX);
  $data = $this->db->query($facebook_product_group_col_exists_sql)->rows;
  if (sizeof($data) === 0) {
    $create_facebook_product_group_sql = sprintf("ALTER TABLE `%s`." .
      "`%sfacebook_product` ADD COLUMN " .
      "(`facebook_product_group_id` VARCHAR(20) NOT NULL DEFAULT 0) ",
      DB_DATABASE,
      DB_PREFIX);
    $this->db->query($create_facebook_product_group_sql);
  }

  // checks if existing oc_facebook_product columns are
  // storing the fb ids as bigint
  // if so, change the columns to varchar
  $facebook_product_id_data_type_sql = sprintf("SELECT DATA_TYPE " .
    "FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '%s' " .
    "AND TABLE_NAME = '%sfacebook_product' " .
    "AND COLUMN_NAME = 'facebook_product_id'",
    DB_DATABASE,
    DB_PREFIX);
  $data = $this->db->query($facebook_product_id_data_type_sql)->rows;
  if (sizeof($data) === 1 && strtolower($data[0]['DATA_TYPE']) === 'bigint') {
    $alter_facebook_product_id_sql = sprintf("ALTER TABLE `%s`." .
      "`%sfacebook_product` MODIFY COLUMN " .
      "`facebook_product_id` VARCHAR(20) NOT NULL DEFAULT '' ",
      DB_DATABASE,
      DB_PREFIX);
    $this->db->query($alter_facebook_product_id_sql);
    $alter_facebook_product_group_id_sql = sprintf("ALTER TABLE `%s`." .
      "`%sfacebook_product` MODIFY COLUMN " .
      "`facebook_product_group_id` VARCHAR(20) NOT NULL DEFAULT '' ",
      DB_DATABASE,
      DB_PREFIX);
    $this->db->query($alter_facebook_product_group_id_sql);
  }

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

  // delete module folder to prevent path error in lower version
  if (version_compare(VERSION , '2.0.3.1') <= 0) {
    unlink(DIR_APPLICATION . '/controller/extension/module/facebookadsextension_installer.php');
    rmdir(DIR_APPLICATION . '/controller/extension/module/');
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
    $data['fae_link'] = $this->url->link(
        'extension/facebookadsextension',
        $data['token_string'],
        true);

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

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
