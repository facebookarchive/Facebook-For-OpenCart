<?php
// Copyright 2017-present, Facebook, Inc.
// All rights reserved.

// This source code is licensed under the license found in the
// LICENSE file in the root directory of this source tree.

/**
 * @package FacebookPixelPlugin
 */

namespace FacebookPixelPlugin\Core;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/eventidgenerator.php';
require_once __DIR__ . '/facebookcommonutils.php';

use \FacebookCommonUtils;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\UserData;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Util;
use FacebookPixelPlugin\Core\EventIdGenerator;

class ServerEventFactory {
	private static $faeLog;

	public static function newEvent(
		$event_name
	) {
		$user_data = (new UserData())
			->setClientIpAddress(Util::getIpAddress())
			->setClientUserAgent(Util::getHttpUserAgent())
			->setFbp(Util::getFbp())
			->setFbc(Util::getFbc());

		$event = (new Event())
			->setEventName($event_name)
			->setEventTime(time())
			->setEventId(EventIdGenerator::guidv4())
			->setEventSourceUrl(Util::getRequestUri())
			->setUserData($user_data)
			->setCustomData(new CustomData());

		return $event;
	}

	public static function safeCreateEvent(
		$event_name,
		$callback,
		$arguments,
		$user_pii_data,
		$config
	) {
		$event = self::newEvent($event_name);

		try {
			$data = call_user_func_array($callback, $arguments);

			if ($config->get(FacebookCommonUtils::FACEBOOK_PIXEL_USE_PII) === 'true') {
				$enabled_amm_fields = explode(
					',', 
					$config->get(FacebookCommonUtils::FACEBOOK_PIXEL_ENABLED_AAM_FIELDS)
				);
				$user_data = $event->getUserData();
				
				if (!empty($user_pii_data['em']) && in_array('em', $enabled_amm_fields)) {
					$user_data->setEmail($user_pii_data['em']);
				}

				if (!empty($user_pii_data['fn']) && in_array('fn', $enabled_amm_fields)) {
					$user_data->setFirstName($user_pii_data['fn']);
				}

				if (!empty($user_pii_data['ln']) && in_array('ln', $enabled_amm_fields)) {
					$user_data->setLastName($user_pii_data['ln']);
				}

				if (!empty($user_pii_data['ph']) && in_array('ph', $enabled_amm_fields)) {
					$user_data->setPhone($user_pii_data['ph']);
				}
			}

			$custom_data = $event->getCustomData();

			if (!empty($data['currency'])) {
				$custom_data->setCurrency($data['currency']);
			}

			if (!empty($data['value'])) {
				$custom_data->setValue($data['value']);
			}

			if (!empty($data['content_ids'])) {
				$custom_data->setContentIds($data['content_ids']);
			}

			if (!empty($data['content_type'])) {
				$custom_data->setContentType($data['content_type']);
			}
		} catch (\Exception $e) {
			if (empty(self::$faeLog)) {
				self::$faeLog = new \Log(FacebookCommonUtils::FAE_LOG_FILENAME);
			}
			self::$faeLog->write('ServerEventFactory safeCreateEvent error: ' . $e->getMessage());
		}

		return $event;
	}
}
