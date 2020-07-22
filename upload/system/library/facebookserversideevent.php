<?php
/*
 * Copyright (C) 2017-present, Facebook, Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * @package FacebookPixelPlugin
 */

namespace FacebookPixelPlugin\Core;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/facebookcommonutils.php';

use \FacebookCommonUtils;
use FacebookAds\Api;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

class FacebookServerSideEvent {
	private static $instance = null;
	private static $faeLog = null;
	private static $fbutils = null;
	private $tracked_events = [];

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new FacebookServerSideEvent();
			self::$faeLog = new \Log(FacebookCommonUtils::FAE_LOG_FILENAME);
			self::$fbutils = new FacebookCommonUtils();
		}

		return self::$instance;
	}

	private function getIsCookieConsentAllowed() {
		return empty($_COOKIE['fb_cookieconsent_status']) // cookie bar is hidden on store website
			|| $_COOKIE['fb_cookieconsent_status'] !== 'deny'; // cookie bar displayed and user not explicitly opt out
	}

	public function track($event, $config) {
		if(empty($event) || !$this->getIsCookieConsentAllowed()) {
			return;
		}
		
		$this->tracked_events[] = $event;

		if ($config->get(FacebookCommonUtils::FACEBOOK_USE_S2S) === 'true') {
			self::send($this->tracked_events, $config);
		}
	}

	public function getTrackedEvents() {
		return $this->tracked_events;
	}

	public static function send($events, $config) {
		if (empty($events)) {
			return;
		}

		try {
			$pixel_id = $config->get(FacebookCommonUtils::FACEBOOK_PIXEL_ID);
			$access_token = $config->get(FacebookCommonUtils::FACEBOOK_SYSTEM_USER_ACCESS_TOKEN);
			$agent = self::$fbutils->getAgentString();

			$api = Api::init(null, null, $access_token);

			$request = (new EventRequest($pixel_id))
						->setEvents($events)
						->setPartnerAgent($agent);

			$response = $request->execute();
		} catch (\Exception $e) {
			self::$faeLog->write('FacebookServerSideEvent send error: ' . $e->getMessage());
		}
	}
}
