<?php

if (!defined('ABSPATH')) {
    exit;
}

function contlo_rebuild_cart_page()
{

    if (is_contlo_cart_rebuild_url()) {
        contlo_rebuild_cart();
    }
}

function is_contlo_cart_rebuild_url()
{
    return isset($_GET) && isset($_GET['action']) && $_GET['action'] === 'contloRebuildCart';
}

function contlo_rebuild_cart()
{
    global $woocommerce;

    if (empty($_GET['contloCart'])) {
        exit;
    }
    $encoded_contlo_cart = sanitize_text_field(wp_unslash($_GET['contloCart']));

    $woocommerce->cart->empty_cart(true);
    $woocommerce->cart->get_cart();

    $contlo_cart = json_decode(base64_decode($encoded_contlo_cart), true);

    $cart_products = $contlo_cart['products'];

    foreach ($cart_products as $product) {
        $woocommerce->cart->add_to_cart(
            $product['product_id'],
            $product['quantity'],
            $product['variation_id'],
            $product['variation']
        );
    }

    $redirect_url = wc_get_cart_url();

    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url = add_query_arg(wp_unslash($_SERVER['QUERY_STRING']), '', $redirect_url); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    }

    $redirect_url = remove_query_arg(array('action', 'contloCart'), $redirect_url);
    wp_redirect(esc_url_raw($redirect_url));

    exit;
}

?>