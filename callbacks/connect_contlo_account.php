<?php
function validate_contlo_connect_token(WP_REST_Request $request)
{
    $body = json_decode($request->get_body(), true);

    if (!isset($body['connect_token'])) {
        return new WP_Error(
            'contlo_missing_connect_token',
            'Missing connect token in request.',
            array('status' => 400)
        );
    }

    $token = get_option('contlo_connect_token', '');

    if ($token === '') {
        return new WP_Error(
            'contlo_connect_denied',
            'Connect token is already used.',
            array('status' => 403)
        );
    }

    if ($token !== $request['connect_token']) {
        return new WP_Error(
            'contlo_incorrect_connect_token',
            'Connect token is incorrect.',
            array('status' => 400)
        );
    }

    return true;
}

function connect_contlo_account(WP_REST_Request $request)
{
    $body = json_decode($request->get_body(), true);

    if (!isset($body['contlo_api_key'])) {
        return new WP_Error(
            'contlo_missing_required_properties',
            'Missing required properties in request body.',
            array('status' => 400)
        );
    }

    delete_option('contlo_connect_token');
    update_option('contlo_api_key', $body['contlo_api_key']);
    update_option('store_public_key', $body['store_public_key']);
    ContloLogger::debug("contlo account connected for " . get_home_url() . ", keys are set");

    return array('success' => true);
}

?>