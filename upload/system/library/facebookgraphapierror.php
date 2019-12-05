<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

class FacebookGraphAPIError {
  const ACCESS_TOKEN_EXCEPTION_CODE = 190;
  const DUPLICATE_RETAILER_ID_EXCEPTION_CODE = 10800;
  const INVALID_ID_EXCEPTION_CODE = 100;
  const INVALID_ID_EXCEPTION_SUBCODE = 33;

  const DEFAULT_ACCESS_TOKEN_EXCEPTION_MESSAGE = 'Error using the access token to make API calls';

  public function __construct() {
  }

  private function getErrorCode($result) {
    return (isset($result['error']['code']))
      ? $result['error']['code']
      : null;
  }

  private function getErrorSubCode($result) {
    return (isset($result['error']['error_subcode']))
      ? $result['error']['error_subcode']
      : null;
  }

  private function getErrorMessage($result) {
    return (isset($result['error']['message']))
      ? $result['error']['message']
      : null;
  }

  public function checksForAccessTokenErrorAndThrowException(
    $result) {
    if ($this->getErrorCode($result) === self::ACCESS_TOKEN_EXCEPTION_CODE) {
      $error_message = ($this->getErrorMessage($result))
        ? $this->getErrorMessage($result)
        : self::DEFAULT_ACCESS_TOKEN_EXCEPTION_MESSAGE;
      throw new Exception(
        $error_message,
        self::ACCESS_TOKEN_EXCEPTION_CODE);
    }
  }

  // checks if there is already an existing
  // product object (product or product group) of the retailer_id on FB
  // this is indicated by error code 10800
  public function isDuplicateRetailerError($result) {
    return ($this->getErrorCode($result) ==
      self::DUPLICATE_RETAILER_ID_EXCEPTION_CODE);
  }

  // gets the duplicated product group retailer id
  public function getDuplicateProductGroupRetailerId($result) {
    return ($this->isDuplicateRetailerError($result)
      && isset($result['error']['error_data']['product_group_id']))
      ? $result['error']['error_data']['product_group_id']
      : null;
  }
}
