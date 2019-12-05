<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

require_once(DIR_APPLICATION.'../system/library/facebookproducttrait.php');
require_once(DIR_APPLICATION.'../system/library/controller/extension/facebookproductfeed.php');

class ControllerExtensionFacebookFeed extends Controller {
    public function __construct($registry){
        parent::__construct($registry);
        $this->facebook_product_feed_controller = new ControllerExtensionFacebookProductFeed($registry);
    }

    public function index() {
    }
    
    public function genFeed() {
        return $this->facebook_product_feed_controller->genFeed();
    }

    public function genFeedNow() {
        return $this->facebook_product_feed_controller->genFeed(true);
    }
    
    public function genFeedPing() {
        $time = $this->facebook_product_feed_controller->estimateFeedGenerationTime();
        $this->response->addHeader('Content-type: text/plain');
        $this->response->setOutput(round($time));

        // This will call the genAction method above in an async request
        // so that we can still return a response from the ping action.
        try {
            $url = HTTP_SERVER.'index.php?route=extension/facebookfeed/genFeed&from=genFeedPing';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            // We expect the result to time out.
            $this->faeLog->write('genFeedPing error'. $e);
        }
	}
}