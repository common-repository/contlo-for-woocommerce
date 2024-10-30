<?php

if (!defined('ABSPATH')) {
    exit;
}

function contlo_checkout_started()
{
    $email = isset($_GET['email']) ? sanitize_email(wp_unslash($_GET['email'])) : '';
    $phone_number = isset($_GET['phone_number']) ? sanitize_text_field(wp_unslash($_GET['phone_number'])) : '';
    $first_name = isset($_GET['first_name']) ? sanitize_text_field(wp_unslash($_GET['first_name'])) : '';
    $last_name = isset($_GET['last_name']) ? sanitize_text_field(wp_unslash($_GET['last_name'])) : '';
    $company = isset($_GET['company']) ? sanitize_text_field(wp_unslash($_GET['company'])) : '';
    $country_code = isset($_GET['country']) ? sanitize_text_field(wp_unslash($_GET['country'])) : '';
    $address_line_1 = isset($_GET['address_line_1']) ? sanitize_text_field(wp_unslash($_GET['address_line_1'])) : '';
    $address_line_2 = isset($_GET['address_line_2']) ? sanitize_text_field(wp_unslash($_GET['address_line_2'])) : '';
    $city = isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : '';
    $state_code = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    $pin_code = isset($_GET['pin_code']) ? sanitize_text_field(wp_unslash($_GET['pin_code'])) : '';

    $country = contlo_get_country_from_country_code($country_code);
    $state = contlo_get_state_from_state_code($country_code, $state_code);

    $address = array(
        'first_name' => $first_name,
        'last_name' => $last_name,
        'company' => $company,
        'country_code' => $country_code,
        'country' => $country,
        'address_line_1' => $address_line_1,
        'address_line_2' => $address_line_2,
        'city' => $city,
        'state_code' => $state_code,
        'state' => $state,
        'pin_code' => $pin_code
    );

    $cart = WC()->cart;
    $request_body = ContloCartBuilder::build_cart_params($cart);
    $request_body['address'] = $address;
    ContloEventsTracker::trackEvent("woocommerce_checkout_started", $email, $phone_number, $request_body, CONTLO_CHECKOUT_STARTED_URL);
    exit;
}


function contlo_get_country_from_country_code($country_code)
{
    $countries = WC()->countries->get_countries();

    if (isset($countries[$country_code])) {
        $country_name = $countries[$country_code];
        return $country_name;
    } else {
        return $country_code;
    }

}

function contlo_get_state_from_state_code($country_code, $state_code)
{
    $states = WC()->countries->get_states($country_code);

    if (isset($states[$state_code])) {
        $state_name = $states[$state_code];
        return $state_name;
    } else {
        return $state_code;
    }
}

?>