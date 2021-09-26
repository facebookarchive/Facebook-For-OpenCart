<?php
/**
  * Copyright (c) Facebook, Inc. and its affiliates.
  * All rights reserved.
  *
  * This source code is licensed under the license found in the
  * LICENSE file in the root directory of this source tree.
  */

require_once(DIR_SYSTEM . 'library/vendor/facebook_business/vendor/autoload.php');

use FacebookAds\Object\ServerSide\AdsPixelSettings;

class ControllerModuleFacebookBusiness extends Controller {
    private $error = array();

    private $opencart_server_base_url = 'https://opencart-plugin.com';
    private $facebook_app_id = '785409108588782';

    public function index() {
        $data = $this->load->language('module/facebook_business');

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('facebook_business', $this->request->post);

            $this->clearProductFeed();

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->load->model('module/facebook_business');

        // For LWI integration
        $data['business_name'] = $this->config->get('config_name');
        $data['external_business_id'] = HTTPS_CATALOG;
        $data['timezone'] = date_default_timezone_get();
        $data['currency'] = strtoupper(addslashes($this->config->get('config_currency')));

        $plugin_version = $this->model_module_facebook_business->getPluginVersion();

        $data['opencart_iframe_url'] = $this->opencart_server_base_url . '/facebook?'
        . 'external_business_id=' . urlencode(HTTPS_CATALOG)
        . '&business_name=' . addslashes($this->config->get('config_name'))
        . '&feed_url=' . urlencode(HTTPS_CATALOG . 'index.php?route=module/facebook_business/genFeed')
        . '&feed_ping_url=' . urlencode(HTTPS_CATALOG . 'index.php?route=module/facebook_business/genFeedPing')
        . '&timezone=' . date_default_timezone_get()
        . '&currency=' . strtoupper(addslashes($this->config->get('config_currency')))
        . '&version=' . $plugin_version;

        if ($this->config->get('config_maintenance')) {
            $data['error_maintenance_mode'] = $this->language->get('error_maintenance_mode');
        } else {
            $data['error_maintenance_mode'] = '';
        }

        if (!empty($this->config->get('facebook_dia_setting_id'))) {
            $data['opencart_iframe_url'] .= '&merchant_settings_id=' . $this->config->get('facebook_dia_setting_id');
        }

        if (!empty($this->config->get('facebook_fbe_v2_installed'))) {
            $data['opencart_iframe_url'] .= '&fbe_v2_installed=' . $this->config->get('facebook_fbe_v2_installed');
        }

        if (!empty($this->config->get('facebook_use_s2s'))) {
            $data['opencart_iframe_url'] .= '&s2s_configured=' . $this->config->get('facebook_use_s2s');
        }

        $data['access_token'] = $this->config->get('facebook_system_user_access_token');
        $data['opencart_server_base_url'] = $this->opencart_server_base_url;
        $data['facebook_app_id'] = $this->facebook_app_id;
        $data['token'] = $this->session->data['token'];
        $data['redirect_uri'] = $this->url->link('module/facebook_business', 'token=' . $this->session->data['token'], true);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'] . '&type=module', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/facebook_business', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('module/facebook_business', 'token=' . $this->session->data['token'], true);

        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'] . '&type=module', true);

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->request->post['facebook_business_cookie_bar_status'])) {
            $data['facebook_business_cookie_bar_status'] = $this->request->post['facebook_business_cookie_bar_status'];
        } else {
            $data['facebook_business_cookie_bar_status'] = $this->config->get('facebook_business_cookie_bar_status');
        }

        if (isset($this->request->post['facebook_business_sync_specials_status'])) {
            $data['facebook_business_sync_specials_status'] = $this->request->post['facebook_business_sync_specials_status'];
        } else {
            $data['facebook_business_sync_specials_status'] = $this->config->get('facebook_business_sync_specials_status');
        }

        $data['text_plugin_version'] = sprintf($this->language->get('text_plugin_version'), $plugin_version);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('module/facebook_business.tpl', $data));
    }

    public function install() {
        if (!$this->user->hasPermission('modify', 'extension/module')) {
            return;
        }

        $this->load->model('module/facebook_business');

        $this->model_module_facebook_business->install();
    }

    public function uninstall() {
        if (!$this->user->hasPermission('modify', 'extension/module')) {
            return;
        }

        $this->load->model('module/facebook_business');

        $this->model_module_facebook_business->uninstall();
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'module/facebook_business')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function updateSettings() {
        $json = array();

        $this->load->model('module/facebook_business');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['facebook_pixel_id'])) {
                $this->request->post['facebook_use_s2s'] = true;
                $this->request->post['facebook_pixel_use_pii'] = $this->getPixelAAMSettings($this->request->post['facebook_pixel_id']);
                $this->request->post['facebook_pixel_enabled_aam_fields'] = $this->getPixelEnabledAAMFields($this->request->post['facebook_pixel_id']);
                $this->request->post['facebook_last_aam_check_time'] = time();
            }

            $this->model_module_facebook_business->updateFacebookSettings($this->request->post);

            $json['success'] = true;
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteSettings() {
        $json = array();

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->deleteSetting('facebook');

            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getPixelAAMSettings($pixel_id) {
        $settings = AdsPixelSettings::buildFromPixelId($pixel_id);

        if ($settings !== null) {
          return $settings->getEnableAutomaticMatching() ? 'true' : 'false';
        }

        return 'false';
    }

    private function getPixelEnabledAAMFields($pixel_id) {
        $settings = AdsPixelSettings::buildFromPixelId($pixel_id);

        if ($settings !== null) {
          $enabled_aam_fileds = $settings->getEnabledAutomaticMatchingFields();
          return implode(',', $enabled_aam_fileds);
        }

        return '';
    }

    private function clearProductFeed() {
        $feed_file_dir = '';

        if (is_writable(DIR_DOWNLOAD)) {
            $feed_file_dir = DIR_DOWNLOAD;
        } elseif (is_writable(DIR_MODIFICATION)) {
            $feed_file_dir = DIR_MODIFICATION;
        } elseif (is_writable(DIR_LOGS)) {
            $feed_file_dir = DIR_LOGS;
        }

        if ($feed_file_dir) {
            $product_feed_path = $feed_file_dir . 'fbe_product_catalog.csv';

            if (is_file($product_feed_path)) {
                unlink($product_feed_path);
            }
        }
    }
}