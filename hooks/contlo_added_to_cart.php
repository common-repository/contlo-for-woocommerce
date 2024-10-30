<?php

if (!defined('ABSPATH')) {
    exit;
}

function contlo_added_to_cart()
{
    $cart = WC()->cart;
    $request_body = ContloCartBuilder::build_cart_params($cart);
    ContloEventsTracker::trackEvent("woocommerce_added_to_cart", null, null, $request_body, CONTLO_SYNC_CART);
}

?>