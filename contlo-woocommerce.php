<?php
/**
 * Plugin Name: Contlo for Woocommerce
 * Description: This plugin connects your Woocommerce site with your Contlo account.
 * Version: 1.0.2
 * Author: Contlo
 * Author URI: https://contlo.com
 * Developer: Contlo
 * Developer URI: https://developer.contlo.com
 *
 * WC requires at least: 2.2
 * WC tested up to: 7.1
 *
 * Copyright: © 2022 Contlo
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die("not a wordpress environment");
define('CONTLO_APP_URL', 'https://marketing.contlo.com');
define('CONTLO_PLUGIN_INSTALL_URL', CONTLO_APP_URL . '/api/woocommerce/install');
define('CONTLO_LOGIN_URL', CONTLO_APP_URL . '/login');
define('CONTLO_REGISTRATION_URL', CONTLO_APP_URL . '/auth_users/sign_up');
define('CONTLO_SLUG', 'CONTLO_SETTINGS_PAGE');
define('CONTLO_SNIPPET_URL', CONTLO_APP_URL . '/js/contlo_messaging_v3.js?v=3');
define('CONTLO_WC_API_KEY', "Contlo App");
define('CONTLO_PLUGIN_UNINSTALL_URL', CONTLO_APP_URL . '/api/woocommerce/callback/uninstall/uninstall_woocommerce_plugin/');
define('CONTLO_PLUGIN_DEACTIVATE_URL', CONTLO_APP_URL . '/api/woocommerce/callback/deactivate/deactivate_woocommerce_plugin/');
define('CONTLO_SERVICE_WORKER_DIR', 'apps/contlo');

//hooks urls
define('CONTLO_SYNC_CART', CONTLO_APP_URL . '/api/woocommerce/webhooks/cart/sync_cart/');
define('CONTLO_CHECKOUT_STARTED_URL', CONTLO_APP_URL . '/api/woocommerce/webhooks/checkout/checkout_started/');
define('CONTLO_VIEWED_PRODUCT_URL', CONTLO_APP_URL . '/api/woocommerce/webhooks/product/viewed/');


// helpers
require_once plugin_dir_path(__FILE__) . 'helpers/contlo_utils.php';
require_once plugin_dir_path(__FILE__) . 'helpers/contlo_client.php';
require_once plugin_dir_path(__FILE__) . 'helpers/contlo_logger.php';
require_once plugin_dir_path(__FILE__) . 'helpers/contlo_user_directory.php';
require_once plugin_dir_path(__FILE__) . 'helpers/contlo_event_tracker.php';
require_once plugin_dir_path(__FILE__) . 'helpers/contlo_cart_builder.php';
require_once plugin_dir_path(__FILE__) . 'helpers/contlo_uninstall.php';
require_once plugin_dir_path(__FILE__) . 'helpers/request_validator.php';

// views
require_once plugin_dir_path(__FILE__) . 'views/contlo_connect.php';
require_once plugin_dir_path(__FILE__) . 'views/contlo_connected.php';
require_once plugin_dir_path(__FILE__) . 'views/contlo_menu.php';
require_once plugin_dir_path(__FILE__) . 'views/contlo_rebuild_cart_page.php';


// callbacks
require_once plugin_dir_path(__FILE__) . 'callbacks/connect_contlo_account.php';
require_once plugin_dir_path(__FILE__) . 'callbacks/is_contlo_account_connected.php';
require_once plugin_dir_path(__FILE__) . 'callbacks/contlo_test_api.php';
require_once plugin_dir_path(__FILE__) . 'callbacks/contlo_get_logs.php';
require_once plugin_dir_path(__FILE__) . 'callbacks/contlo_enable_logs.php';
require_once plugin_dir_path(__FILE__) . '/contlo_register_callbacks.php';


// hooks
require_once plugin_dir_path(__FILE__) . 'hooks/contlo_added_to_cart.php';
require_once plugin_dir_path(__FILE__) . 'hooks/contlo_checkout_started.php';
require_once plugin_dir_path(__FILE__) . 'hooks/contlo_viewed_product.php';


class ContloPlugin
{

    function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        $this->create_logs_db();
        add_action('init', array($this, 'create_public_directory'));
        add_action('wp_enqueue_scripts', array('ContloUtils', 'createServiceWorker'));
        add_action('admin_enqueue_scripts', array($this, 'load_styles'));
        add_action('admin_menu', array($this, 'show_contlo_menu'));
        contlo_register_callbacks();
        register_uninstall_hook(__FILE__, 'contlo_uninstall');
        $this->ingest_contlo_script();
        $this->register_hooks();
        add_action('wp_loaded', 'contlo_rebuild_cart_page');
        add_action('wp_enqueue_scripts', array($this, 'ingest_checkout_script'));
    }


    public function activate()
    {
        // log info plugin activated
        ContloLogger::info("contlo plugin activated");
        update_option('event_tracking_enabled', 'ENABLED');
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        ContloLogger::info("contlo plugin deactivated");
        update_option('logs_enabled', 'DISABLED');
        update_option('event_tracking_enabled', 'DISABLED');
        ContloClient::contlo_api(CONTLO_PLUGIN_DEACTIVATE_URL . get_option("contlo_api_key"), "POST", array());
        flush_rewrite_rules();
    }

    public function show_contlo_menu()
    {
        $page_title = 'Contlo for Woocommerce';
        $menu_title = 'Contlo';
        $capability = 'manage_options';
        $menu_slug = CONTLO_SLUG;
        $function = 'show_contlo_menu';
        $icon_url   = plugin_dir_url( __FILE__ ) . 'assets/img/contlo-icon.png';

        $position = 3;
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    }

    public function load_styles()
    {
        if (isset($_GET['page'])) {
            if (in_array($_GET['page'], array(CONTLO_SLUG), true)) {
                wp_enqueue_style(
                    'contlo-global.css',
                    plugin_dir_url(__FILE__) . 'assets/styles/contlo-global.css?' . time()
                );
            }
        }
    }

    public function create_logs_db()
    {
        new ContloLogger();
    }


    public function ingest_contlo_script()
    {
        add_action(
            'wp_footer',
            function () {
                if (ContloUtils::isWooCommercePluginActivated()) {
                    $store_public_key = get_option('store_public_key', null);
                    if ($store_public_key !== null) {
                        ?>
                        <!-- <script>window.CONTLO_ENV = 'development'</script> -->
                        <script>window.CONTLO_ENV = 'production'</script>
                        <script type="text/javascript">
                            !function () {
                                var e = document.createElement("script");
                                e.type = "text/javascript", e.async = !0, e.src = "<?php echo esc_url(CONTLO_SNIPPET_URL) . "&shop_id=" . esc_attr($store_public_key); ?>";
                                var t = document.getElementsByTagName("script")[0];
                                t.parentNode.insertBefore(e, t)
                            }();
                        </script>
                        <?php
                    }
                }
            }
        );
    }

    public function ingest_checkout_script()
    {

        if ((function_exists('is_checkout') && is_checkout()) || (strpos($_SERVER['REQUEST_URI'], '/checkout/') !== false)) {
            $file_name = 'contlo-checkout-script.js';
            $file_path = plugin_dir_url(__FILE__) . 'assets/scripts/' . $file_name . '?' . time();
            wp_register_script($file_name, $file_path, array(), '1.0.2', true);
            wp_localize_script(
                $file_name,
                'contlo_checkout_vars',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'external_user_id' => ContloUserDirectory::getExternalUserId(),
                )
            );
            wp_enqueue_script($file_name, $file_path, array(), '1.0.0', true);

        }
    }

    public function create_public_directory()
    {
        if (!file_exists(CONTLO_SERVICE_WORKER_DIR)) {
            mkdir(CONTLO_SERVICE_WORKER_DIR, 0777, true);
        }
    }


    public function register_hooks()
    {
        add_action('woocommerce_add_to_cart', 'contlo_added_to_cart');
        add_action('wp_ajax_track_started_checkout_event', 'contlo_checkout_started');
        add_action('wp_ajax_nopriv_track_started_checkout_event', 'contlo_checkout_started');
        add_action('woocommerce_after_single_product', 'contlo_viewed_product');
    }
}


if (class_exists('ContloPlugin')) {
    $contloPlugin = new ContloPlugin();
}