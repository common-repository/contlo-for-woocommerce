<?php
function contlo_register_callbacks()
{
    add_action(
        'rest_api_init',
        function () {
            register_rest_route(
                'contlo-api/v1',
                '/connect_contlo_account',
                array(
                    'methods' => 'POST',
                    'callback' => 'connect_contlo_account',
                    'permission_callback' => 'validate_contlo_connect_token',
                )
            );
            register_rest_route(
                'contlo-api/v1',
                '/connected',
                array(
                    'methods' => 'GET',
                    'callback' => 'is_contlo_account_connected',
                    'permission_callback' => '__return_true',
                )
            );
            register_rest_route(
                'contlo-api/v1',
                '/contlo_test_api',
                array(
                    'methods' => 'POST',
                    'callback' => 'contlo_test_api',
                    'permission_callback' => '__return_true',
                )
            );

            register_rest_route(
                'contlo-api/v1',
                '/uninstall',
                array(
                    'methods' => 'POST',
                    'callback' => 'disconnect_contlo',
                    'permission_callback' => '__return_true',
                )
            );

            register_rest_route(
                'contlo-api/v1',
                '/fetch_logs',
                array(
                    'methods' => 'GET',
                    'callback' => 'contlo_get_logs',
                    'permission_callback' => 'validate_request_to_plugin',
                )
            );

            register_rest_route(
                'contlo-api/v1',
                '/enable_logs',
                array(
                    'methods' => 'POST',
                    'callback' => 'contlo_enable_logs',
                    'permission_callback' => 'validate_request_to_plugin',
                )
            );
        }
    );
}

?>