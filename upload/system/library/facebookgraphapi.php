<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class FacebookGraphAPI {
  const GRAPH_API_URL = 'https://graph.facebook.com/v3.0/';

  private $facebookgraphapierror;

  public function __construct() {
    $this->facebookgraphapierror = new FacebookGraphAPIError();
  }

  private function post($url, $data = null) {
    $curl = curl_init();
    $params = array(
      CURLOPT_URL => $url,
      CURLOPT_POST => 1,
      CURLOPT_RETURNTRANSFER => 1);

    if (!empty($data)) {
      $params[CURLOPT_POSTFIELDS] = $data;
    }

    curl_setopt_array(
      $curl,
      $params);

    $result = json_decode(curl_exec($curl), true);
    $this->facebookgraphapierror->checksForAccessTokenErrorAndThrowException($result);
    return $result;
  }

  private function appendAccessToken($url, $facebook_page_token) {
    return $url . '?access_token=' . $facebook_page_token;
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

  public function createPixelSignatureKeys(
    $pixel_id,
    $facebook_page_token) {
    $url = self::GRAPH_API_URL .
      (string)$pixel_id .
      '/create_server_to_server_keys';
    $url = $this->appendAccessToken($url, $facebook_page_token);
    // success API call will return {data: [{type: "PRIMARY", key: <key>}, {type: "SECONDARY", key: <key>}]}
    // failure API will return {error: <error message>}
    // we require to put in at least 1 post param for the POST call to work
    return $this->post($url, array('access_token' => $facebook_page_token));
  }

}
