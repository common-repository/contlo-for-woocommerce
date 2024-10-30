<?php

if (!defined('ABSPATH')) {
    exit;
}

function contlo_get_logs(WP_REST_Request $request)
{
    $logs = ContloLogger::contlo_get_logs();
    $delete_logs = $request->get_param('delete_logs');
    if ($delete_logs == 'true') {
        ContloLogger::delete_logs();
    }
    return $logs;
}

?>