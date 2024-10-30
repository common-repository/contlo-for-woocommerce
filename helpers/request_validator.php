<?php

if (!defined('ABSPATH')) {
    exit;
}

function validate_request_to_plugin(WP_REST_Request $request)
{
    $external_key = $request->get_param('external_key');

    if ($external_key == null) {
        return new WP_Error(
            'contlo_missing_external_key',
            'Missing external key',
            array('status' => 400)
        );
    }

    $contlo_api_key = get_option('contlo_api_key', '');

    if ($contlo_api_key !== $external_key) {
        return new WP_Error(
            'contlo_incorrect_api_key',
            'external key is not correct.',
            array('status' => 400)
        );
    }

    return true;

}

?>