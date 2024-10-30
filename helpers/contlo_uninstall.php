<?php

if (!defined('ABSPATH')) {
    exit;
}

function contlo_uninstall()
{
    ContloLogger::delete_logs();
    ContloClient::contlo_api(CONTLO_PLUGIN_UNINSTALL_URL . get_option("contlo_api_key"), "POST", array());
    delete_option('store_public_key');
    delete_option('contlo_api_key');
    delete_option('logs_enabled');
    delete_option('event_tracking_enabled');
}


function revoke_contlo_woo_api_keys()
{

    global $wpdb;

    $api_keys = $wpdb->get_results("SELECT `key_id` FROM {$wpdb->prefix}woocommerce_api_keys WHERE `description` LIKE '" . CONTLO_WC_API_KEY . " - API %'");

    if (sizeof($api_keys) <= 0) {
        return;
    }

    foreach ($api_keys as $api_key) {
        global $wpdb;
        $delete = $wpdb->delete($wpdb->prefix . 'woocommerce_api_keys', array('key_id' => $api_key->key_id), array('%d'));
    }
}

function delete_contlo_webhooks()
{
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        return;
    }

    $webhook_data_store = WC_Data_Store::load('webhook');
    $num_webhooks = $webhook_data_store->get_count_webhooks_by_status();
    $count = array_sum($num_webhooks);

    if ($count <= 0) {
        return;
    }

    $webhook_ids = $webhook_data_store->get_webhooks_ids();

    foreach ($webhook_ids as $webhook_id) {
        $webhook = wc_get_webhook($webhook_id);
        if (!$webhook) {
            continue;
        }

        $is_contlo_delivery_url = false !== strpos($webhook->get_delivery_url(), 'contlo');
        if ($is_contlo_delivery_url) {
            $webhook_data_store->delete($webhook);
        }
    }
}

?>