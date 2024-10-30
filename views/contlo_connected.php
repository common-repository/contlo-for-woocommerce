<?php

if (!defined('ABSPATH')) {
    exit;
}

function displayContloConnected()
{
    ?>

    <div class="contlo_connected_page_main_container">
        <div class="left_panel">
            <p class="primary_text">You are connected to Contlo</p>
            <p class="secondary_text">Create your own Brand AI model & use it to drive personalized automated generative
                marketing activities across customer touchpoints. Log in to authorize an account connection. New to
                Contlo and want to learn more?
                <a target="_blank" href="#">
                    Check out How to integrate with WooCommerce Guide.
                </a>
            </p>
            <a target="_blank" href="<?php echo esc_url(CONTLO_APP_URL) ?>">
                <button class="connect_account_button">Go to Contlo</button>
            </a>
        </div>
    </div>

    <?php
}

?>