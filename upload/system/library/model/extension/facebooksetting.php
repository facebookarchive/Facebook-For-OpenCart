<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ModelExtensionFacebookSetting extends Model {
  public function updateSettings($data) {
    foreach ($data as $key => $value) {
      $this->db->query("DELETE FROM `" .
        DB_PREFIX .
        "setting` WHERE `code` = 'facebook' AND `key` = '" .
        $this->db->escape($key) .
        "'");
      $this->db->query("INSERT INTO " .
        DB_PREFIX .
        "setting SET store_id = '0', `code` = 'facebook', `key` = '" .
        $this->db->escape($key) .
        "', `value` = '" .
        $this->db->escape($value) .
        "'");
    }
  }

  public function getSettings() {
    $settings = array();
    $query = $this->db->query("SELECT * FROM " .
      DB_PREFIX .
      "setting WHERE `code` = 'facebook'");
    foreach ($query->rows as $result) {
      $settings[$result['key']] = $result['value'];
    }
    return $settings;
  }

  public function getSetting($setting_key) {
    $settings = array();
    $query = $this->db->query("SELECT * FROM " .
      DB_PREFIX .
      "setting WHERE `code` = 'facebook' " .
      "AND `key` = '" . $this->db->escape($setting_key) . "'");
    return (isset($query->row['value']))
      ? $query->row['value']
      : '';
  }

  public function deleteSettings() {
    $this->db->query("DELETE FROM `" .
      DB_PREFIX .
      "setting` WHERE `code` = 'facebook'");
  }

  public function deleteSetting($setting_key) {
    $this->db->query("DELETE FROM `" .
      DB_PREFIX .
      "setting` WHERE `code` = 'facebook' " .
      "AND `key` = '" . $this->db->escape($setting_key) . "'");
  }
}
