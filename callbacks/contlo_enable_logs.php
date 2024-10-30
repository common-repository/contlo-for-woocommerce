<?php
function contlo_enable_logs(WP_REST_Request $request)
{
    $body = json_decode($request->get_body(), true);
    $logs_enabled = $body['enabled'] == 'true' ? 'ENABLED' : 'DISABLED';
    update_option("logs_enabled", $logs_enabled);
    return true;
}

?>