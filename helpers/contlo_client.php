<?php

if (!defined('ABSPATH')) {
    exit;
}

class ContloClient
{

    public static function contlo_api($link, $method = 'POST', $postfields = array())
    {

        $apiKey = get_option('contlo_api_key', null);
        if (is_array($postfields) && isset($postfields['apiKey'])) {
            $apiKey = $postfields['apiKey'];
        }
        $result = array();
        $data_string = array();

        if (!empty($postfields)) {
            $data_string = json_encode($postfields, JSON_UNESCAPED_SLASHES);
        }

        $timeout = ini_get('max_execution_time');
        if ($timeout > 10 && $timeout <= 30) {
            $timeout = $timeout - 2;
        } else {
            $timeout = 30;
        }

        $headers = array(
            'CONTLO_API_KEY' => $apiKey,
        );

        switch ($method) {
            case 'GET':
                $api_response = wp_remote_get(
                    $link,
                    array(
                        'timeout' => $timeout,
                        'redirection' => 3,
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => $headers,
                        'cookies' => array(),
                    )
                );
                break;
            default:
                $api_response = wp_remote_post(
                    $link,
                    array(
                        'method' => $method,
                        'timeout' => $timeout,
                        'redirection' => 3,
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => $headers,
                        'body' => $data_string,
                        'cookies' => array(),
                    )
                );
        }

        if (is_wp_error($api_response)) {
            $result['code'] = '500';
            $result['response'] = $api_response->get_error_message();
        } else {
            $result['code'] = $api_response['response']['code'];
            $result['response'] = $api_response['body'];
        }

        return $result;
    }

}

?>