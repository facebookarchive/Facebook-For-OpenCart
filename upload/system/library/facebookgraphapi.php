<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class FacebookGraphAPI {
  const GRAPH_API_URL = 'https://graph.facebook.com/v2.10/';

  private $facebookgraphapierror;

  public function __construct() {
    $this->facebookgraphapierror = new FacebookGraphAPIError();
  }

  private function get($url) {
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1));
    $result = json_decode(curl_exec($curl), true);
    $this->facebookgraphapierror->checksForAccessTokenErrorAndThrowException(
      $result);
    return $result;
  }

  private function post($url, $data) {
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $url,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_RETURNTRANSFER => 1));
    $result = json_decode(curl_exec($curl), true);
    $this->facebookgraphapierror->checksForAccessTokenErrorAndThrowException($result);
    return $result;
  }

  private function delete($url) {
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_RETURNTRANSFER => 1));
    $result = json_decode(curl_exec($curl), true);
    $this->facebookgraphapierror->checksForAccessTokenErrorAndThrowException($result);
    return $result;
  }

  private function appendAccessToken($url, $facebook_page_token) {
    return $url . '?access_token=' . $facebook_page_token;
  }

  public function createProductItem(
    $facebook_catalog_id,
    $data,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL . (string)$facebook_catalog_id . '/products';
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {id: <product id>}
    // failure API will return {error: <error message>}
    return $this->post($url, $data);
  }

  public function updateProductItem(
    $facebook_product_id,
    $data,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL . (string)$facebook_product_id;
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {success: true}
    // failure API will return {error: <error message>}
    return $this->post($url, $data);
  }

  public function deleteProductItem(
    $facebook_product_id,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL . (string)$facebook_product_id;
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {success: true}
    // failure API will return {error: <error message>}
    return $this->delete($url);
  }

  public function fblog(
    $ems_id,
    $facebook_page_token,
    $message,
    $object = array(),
    $error = false) {
    if (!$ems_id) {
      return array('success' => false);
    }

    $url = self::GRAPH_API_URL . (string)$ems_id . '/log_events';
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {success: true}
    $message = json_encode(array(
      'message' => $message,
      'object' => json_encode($object)
    ));
    $data = array(
      'message'=> $message,
      'error' => $error);
    return $this->post($url, $data);
  }

  public function createProductGroup(
    $facebook_catalog_id,
    $data,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL .
      (string)$facebook_catalog_id .
      '/product_groups';
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {id: <product group id>}
    // failure API will return {error: <error message>}
    return $this->post($url, $data);
  }

  public function deleteProductGroup(
    $facebook_product_group_id,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL . (string)$facebook_product_group_id;
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {success: true}
    // failure API will return {error: <error message>}
    return $this->delete($url);
  }

  public function createFeed(
    $facebook_catalog_id,
    $data,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL .
      (string)$facebook_catalog_id .
      '/product_feeds';
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {id: <product feed id>}
    // failure API will return {error: <error message>}
    return $this->post($url, $data);
  }

  public function createUpload(
    $facebook_feed_id,
    $feed_filename,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL .
      (string)$facebook_feed_id .
      '/uploads';
    $file = (version_compare(PHP_VERSION, '5.5.0', '>='))
      ? new CurlFile($feed_filename, 'text/csv')
      : "@" . $feed_filename .
        ";type=text/plain" .
        ";filename=" . basename($feed_filename);
    $data = array('file' => $file);
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {id: <product feed upload id>}
    // failure API will return {error: <error message>}
    return $this->post($url, $data);
  }

  public function getUploadStatus(
    $facebook_upload_id,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL .
      (string)$facebook_upload_id;
    $url = $this->appendAccessToken($url, $facebook_page_token);
    $url .= '&fields=end_time';
    // success API call will return
    // {id: <upload id>, end_time: <time when upload completes>}
    // failure API will return {error: <error message>}
    return $this->get($url);
  }

  public function getFacebookProductId(
    $facebook_catalog_id,
    $product_id,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL .
      'catalog:' . (string)$facebook_catalog_id .
      ':' . base64_encode($product_id);
    $url = $this->appendAccessToken($url, $facebook_page_token);
    $url .= '&fields=id,product_group{id}';
    // success API call will return
    // {id: <fb product id>, product_group{id} <fb product group id>}
    // failure API will return {error: <error message>}
    return $this->get($url);
  }

  public function updateProductItemUsingBase64ProductId(
    $facebook_catalog_id,
    $product_id,
    $data,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL .
      'catalog:' . (string)$facebook_catalog_id .
      ':' . base64_encode($product_id);
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {success: true}
    // failure API will return {error: <error message>}
    return $this->post($url, $data);
  }

  // used for verification to ensure the validity of access token
  public function getFacebookPageId(
    $page_id,
    $facebook_page_token) {
    // success API call will return {id: <page id>}
    // failure API will return {error: <error message>}
    return $this->getFacebookId($page_id, $facebook_page_token);
  }

  // used for verification to ensure the validity of catalog id
  public function getFacebookCatalogId(
    $catalog_id,
    $facebook_page_token) {
    // success API call will return {id: <catalog id>}
    // failure API will return {error: <error message>}
    return $this->getFacebookId($catalog_id, $facebook_page_token);
  }

  private function getFacebookId(
    $id,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL . $id;
    $url = $this->appendAccessToken($url, $facebook_page_token);
    $url .= '&fields=id';
    // success API call will return {id: <id>}
    // failure API will return {error: <error message>}
    return $this->get($url);
  }
}
