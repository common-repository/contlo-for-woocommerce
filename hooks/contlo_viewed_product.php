<?php

if (!defined('ABSPATH')) {
    exit;
}

function contlo_viewed_product()
{
    global $product;
    $request_body = array(
        'product_id' => $product->get_id(),
        'product_name' => $product->get_name(),
        'product_price' => $product->get_price(),
        'product_sku' => $product->get_sku(),
        'product_stock_status' => $product->get_stock_status(),
        'product_url' => urlencode(get_permalink($product->get_id())),
        'current_url' => urlencode("http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]")
    );
    ContloEventsTracker::trackEvent("woocommerce_viewed_product", null, null, $request_body, CONTLO_VIEWED_PRODUCT_URL);

}

?>