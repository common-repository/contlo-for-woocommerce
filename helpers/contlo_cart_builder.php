<?php

if (!defined('ABSPATH')) {
    exit;
}

class ContloCartBuilder
{
    public static function build_cart_params($cart)
    {
        $items = array();

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $data = $cart_item['data'];
            $lineItem = array(
                'product_id' => $cart_item['product_id'],
                'variation_id' => $cart_item['variation_id'],
                'quantity' => $cart_item['quantity'],
                'link' => $data->get_permalink($cart_item),
                'attributes' => $cart_item['variation'],
            );
            array_push($items, $lineItem);
        }

        $cart->calculate_totals();

        $request_body = array(
            'raw' => array(
                'currency' => get_woocommerce_currency(),
                'currency_symbol' => get_woocommerce_currency_symbol(),
                'items' => $items,
                'applied_coupons' => $cart->get_applied_coupons(),
                'totals' => $cart->get_totals()
            ),
            'item_count' => $cart->get_cart_contents_count(),
            'cart_hash' => WC()->cart->get_cart_hash(),
            'checkout_url' => ContloCartBuilder::build_checkout_url($cart),
        );

        return $request_body;

    }

    private static function build_checkout_url($cart)
    {
        $products = array();

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = array(
                'product_id' => $cart_item['product_id'],
                'quantity' => $cart_item['quantity'],
                'variation_id' => $cart_item['variation_id'],
                'variation' => $cart_item['variation'],
            );

            array_push($products, $product);
        }

        $cart_to_recover = array('products' => $products);
        $base64_encoded = base64_encode(json_encode($cart_to_recover));

        return urlencode(home_url('?action=contloRebuildCart&contloCart=' . $base64_encoded));
    }
}

?>