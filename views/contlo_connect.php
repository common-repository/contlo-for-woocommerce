<?php

if (!defined('ABSPATH')) {
    exit;
}

function displayContloConnect()
{
    ?>
    <div class="main_container">
        <div class="left_panel">
            <p class="primary_text">Connect your Contlo account to use the Contlo x WooCommerce Integration.</p>
            <p class="secondary_text">Create your own Brand AI model & use it to drive personalized automated generative
                marketing activities across customer touchpoints. Log in to authorize an account connection. New to
                Contlo and want to learn more?
                <a target="_blank" href="#">
                    Check out How to integrate with WooCommerce Guide.
                </a>
            </p>
            <div class="contlo_button_container">
                <div class="contlo_connect_page_button_container">
                    <a target="_blank" href="<?php echo esc_url(generate_contlo_registration_url()); ?>">
                        <button class="create_account_button">Create account</button>
                    </a>
                </div
                <div class="contlo_connect_page_button_container">
                    <a target="_blank" href="<?php echo esc_url(generate_contlo_install_url()); ?>">
                        <button class="connect_account_button">Connect account</button>
                    </a>
                </div>
            </div>
            <img class="cn_connection_image"
                 src="<?php echo esc_url(plugin_dir_url(__NAMESPACE__)) . 'contlo-for-woocommerce/assets/img/contlowoocommerce.png'; ?>">
        </div>
    </div>

    <script type="text/javascript">
        (function () {
            let interval

            function waitUntilAPIKeyAndRefresh() {

                interval = setInterval(async () => {
                    fetch("<?php echo esc_url(home_url('/wp-json/contlo-api/v1/connected')); ?>").then(r => r.json()).then(connected => {
                        if (!connected) {
                            return
                        }

                        clearInterval(interval)
                        location.reload()
                    })
                }, 1000)
            }

            waitUntilAPIKeyAndRefresh()


        })()
    </script>
    <?php
}


function generate_contlo_install_url()
{
    $token = get_option('contlo_connect_token', '');

    if ($token === '') {
        $token = hash('sha256', time());
        update_option('contlo_connect_token', $token);
    }

    $installUrlParams = array(
        'token' => $token,
        'storeDomain' => parse_url(get_home_url())['host']
    );

    return CONTLO_LOGIN_URL . '?' . http_build_query($installUrlParams);
}


function generate_contlo_registration_url()
{
    $token = get_option('contlo_connect_token', '');

    if ($token === '') {
        $token = hash('sha256', time());
        update_option('contlo_connect_token', $token);
    }

    $registrationUrlParams = array(
        'token' => $token,
        'storeDomain' => parse_url(get_home_url())['host'],
        'woocommerceUserId' => get_current_user_id(),
    );


    return CONTLO_REGISTRATION_URL . '?' . http_build_query($registrationUrlParams);
}

?>