<?php

if (!defined('ABSPATH')) {
    exit;
}

class ContloEventsTracker
{

    public static function trackEvent($event_name = null, $email = null, $phone_number = null, $props = array(), $endpoint)
    {
        $event_tracking_enabled = get_option('event_tracking_enabled');
        if ($event_tracking_enabled == 'DISABLED') {
            return;
        }
        if (!isset($event_name)) {
            return;
        }

        $external_key = get_option('contlo_api_key', null);
        if (!isset($external_key)) {
            ContloLogger::error("external key not found" . $event_name);
            return;
        }

        $external_user_id = ContloUserDirectory::getExternalUserId();
        $event_user_id = ContloUserDirectory::getEventUserId();
        $geolocation = ContloUserDirectory::getGeoLocation();
        $customer_id = ContloUserDirectory::getCurrentLoggedInUser();

        if (!isset($email) && !isset($external_user_id) && !isset($phone_number) && !isset($customer_id)) {
            ContloLogger::error("anonymous user for " . $event_name);
            return;
        }

        $body = array(
            'event_name' => $event_name,
            "event_user_id" => $event_user_id,
            "external_user_id" => $external_user_id,
            "email" => $email,
            "phone_number" => $phone_number,
            "customer_id" => $customer_id,
            "geo_country" => $geolocation,
            'properties' => $props,
        );

        self::track($body, $endpoint . $external_key);
        return;
    }

    private static function track($body = array(), $endpoint)
    {
        try {
            wp_remote_post($endpoint, array('body' => $body));
            return;
        } catch (Exception $e) {
            return;
        }
    }
}
