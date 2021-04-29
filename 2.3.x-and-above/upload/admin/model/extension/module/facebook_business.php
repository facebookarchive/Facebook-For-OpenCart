<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class ModelExtensionModuleFacebookBusiness extends Model {
    private $version = '4.0.2';

    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_to_facebook` (
              `product_to_facebook_id` INT(11) NOT NULL AUTO_INCREMENT,
              `product_id` INT(11) NOT NULL,
              `google_product_category` int(16) NOT NULL DEFAULT 0,
              `condition` varchar(20) NOT NULL,
              `age_group` varchar(50) NOT NULL,
              `color` varchar(255) NOT NULL,
              `gender` varchar(20) NOT NULL,
              `material` varchar(255) NOT NULL,
              `pattern` varchar(255) NOT NULL,
              PRIMARY KEY (`product_to_facebook_id`)
            ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_to_facebook`;");
    }

    public function updateFacebookSettings($data = array()) {
        foreach ($data as $key => $value) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'facebook' AND `key` = '" . $this->db->escape($key) . "'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '0', `code` = 'facebook', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
        }
    }

    public function getFacebookParams($product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_facebook` WHERE product_id = '" . (int)$product_id . "'");

        if ($query->num_rows) {
            return array(
                'facebook_google_product_category'   => $query->row['google_product_category'],
                'facebook_condition'                 => $query->row['condition'],
                'facebook_age_group'                 => $query->row['age_group'],
                'facebook_color'                     => $query->row['color'],
                'facebook_gender'                    => $query->row['gender'],
                'facebook_material'                  => $query->row['material'],
                'facebook_pattern'                   => $query->row['pattern']
            );
        } else {
            return array(
                'facebook_google_product_category'   => '',
                'facebook_condition'                 => '',
                'facebook_age_group'                 => '',
                'facebook_color'                     => '',
                'facebook_gender'                    => '',
                'facebook_material'                  => '',
                'facebook_pattern'                   => ''
            );
        }
    }

    public function isNewExtensionAvailable() {
        $last_check_date = $this->config->get('facebook_last_upgrade_check_date');

        // Check for upgrades once a day only if extension is the latest version
        if ($last_check_date) {
            if (strcmp(date("Y-m-d"), $last_check_date) == 0) {
                return false;
            }
        }
        
        $latest_version = $this->getLatestVersion();

        if ($latest_version > $this->version) {
            return true;
        } else {
            $data = array('facebook_last_upgrade_check_date' => date("Y-m-d"));
            $this->updateFacebookSettings($data);

            return false;
        }
    }

    public function getPluginVersion() {
        return $this->version;
    }

    private function getLatestVersion() {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://api.github.com/repos/facebookincubator/Facebook-for-OpenCart/releases/latest');
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_USERAGENT, "curl");
      
            ob_start();
            curl_exec($curl);
            curl_close($curl);
            $lines = ob_get_contents();
            ob_end_clean();
            $json = json_decode($lines, true);
      
            if (!$json || !isset($json['tag_name'])) {
                return false;
            }
      
            $latest_version = $json['tag_name'];

            return (substr($latest_version, 0, 1) == 'v') ? substr($latest_version, 1) : false;
        } catch (Exception $e) {
            return false;
        }
    }
}