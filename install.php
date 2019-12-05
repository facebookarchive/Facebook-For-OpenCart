<?php
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
    $directory = DIR_APPLICATION . '/../admin/controller/extension/module/';
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
