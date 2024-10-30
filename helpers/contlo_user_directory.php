<?php

if (!defined('ABSPATH')) {
    exit;
}

class ContloUserDirectory
{

    /**
     * @var array values set in running PHP process. When it is needed to set and get value in same process
     */
    private static $contlo_cookie_slug = "_cnt_";

    public static function getPublicKey()
    {
        return self::get('public_key');
    }

    public static function getEventUserId()
    {
        return self::get('event_user_id');
    }

    public static function getExternalUserId()
    {
        return self::get('ext_user_id');
    }

    public static function getGeoLocation()
    {
        return self::get('geo_country');
    }

    public static function getCurrentLoggedInUser()
    {
        $current_user = wp_get_current_user();
        if ($current_user->ID) {
            return $current_user->ID;
        } else {
            return null;
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    private static function get($key)
    {
        $value = null;
        try {
            $value = !empty($_COOKIE[ContloUserDirectory::$contlo_cookie_slug . $key]) ? sanitize_text_field(wp_unslash($_COOKIE[ContloUserDirectory::$contlo_cookie_slug . $key])) : null;
        } catch (Exception $e) {
            ContloLogger::debug("Get cookie. Key " . ContloUserDirectory::$contlo_cookie_slug . $key . " Value: " . $value);
            return $value;
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    private static function set($key, $data)
    {
        ContloLogger::debug("Saving to cookie " . ContloUserDirectory::$contlo_cookie_slug . $key . " Value: " . json_encode($data));
        $host = parse_url(home_url(), PHP_URL_HOST);
        $expiry = strtotime('+1 year');
        setcookie(ContloUserDirectory::$contlo_cookie_slug . $key, $data, $expiry, '/', $host);
    }
}

?>