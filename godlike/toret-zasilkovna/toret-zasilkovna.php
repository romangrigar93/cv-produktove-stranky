<?php
/**
 * Plugin Name: Toret Zásilkovna
 * Plugin URI:  https://toret.cz/produkt/woocommerce-zasilkovna/
 * Description: WooCommerce intergration plugin to connect to Packeta services.
 * Version:     8.4.23
 * Author:      Toret.cz
 * Author URI:  https://toret.cz
 * Text Domain: zasilkovna
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * WC requires at least: 6.7
 * WC tested up to: 10.4.2
 * Requires PHP: 7.4
 * Requires at least: 6.2
 */


use Automattic\WooCommerce\Utilities\FeaturesUtil;
use ToretZasilkovna\Toret\Library\LibraryManager;
use ToretZasilkovna\Toret\Library\Log;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

define('WOOZASILKOVNADIR', plugin_dir_path(__FILE__));
define('WOOZASILKOVNAURL', plugin_dir_url(__FILE__));
define('WOOZASILKOVNANAME', plugin_basename(__FILE__));
define('TORETINCLIB', 'includes/ToretZasilkovnaLib/');
define('TORETINCEXT', 'includes/ToretZasilkovnaLib/ToretWooExtension/');
define('WOOZASILKOVNAVER', 'toret-zasilkovna-8-4-23');
define('TORETZASILKOVNAVERSION', '8.4.23');
define('WOOZASILKOVNASLUG', 'zasilkovna');
define('TORETZASILKOVNASLUG', 'toret-zasilkovna');
define('TORETZASILKOVNA', 1405);
define('TORETZASILKOVNASETTINGS', 'admin.php?page=zasilkovna');
define('TORETZASILKOVNALIC', 'woo-zasilkovna-licence');
define('TORETZASILKOVNALOGTABLE', 'zasilkovna_log');
define('TORETZASILKOVNALOGTABLEVERSION', TORETZASILKOVNAVERSION);
define('TORETZASILKOVNALOGSLUG', 'zasilkovna-log');
define('TORETZASILKOVNALOGPAGE', 'admin.php?page=' . TORETZASILKOVNALOGSLUG);
define('TORET_ZASILKOVNA_PICKUP_WIDGET_URL', 'https://widget.packeta.com/v6/www/js/library.js');
define('TORET_ZASILKOVNA_HD_WIDGET_URL', 'https://hd.widget.packeta.com/www/js/library.js');


/**
 * CUSTOMS DECLARATION
 */
define('TORET_ZASILKOVNA_ENABLE_CUSTOMS', false);
define('TORET_ZASILKOVNA_ENABLE_CUSTOMS_COUNTRIES', array('GB'));
define('TORET_ZASILKOVNA_ENABLE_CUSTOMS_CARRIERS', array("gb-royal-mail-24-hd", "gb-royal-mail-48-hd"));
define('TORET_ZASILKOVNA_CUSTOMS_CARRIER_EAD', array("gb-royal-mail-24-hd" => "carrier", "gb-royal-mail-48-hd" => "carrier"));


/**
 * NATIVE METHODS
 */
define('TORET_ZASILKOVNA_NATIVE_TYPES', array(''));
define('TORET_ZASILKOVNA_NATIVE_SHIPPINGS', array(
    '' => 'z-points',
));

define('TORET_ZASILKOVNA_NATIVE_COUNTRIES', array(
    'cz',
    'sk',
    'hu',
    'pl',
    'ro',
));

/*
 * Others
 */
define('TORET_ZASILKOVNA_HD_WIDGET_COUNTRIES', array('CZ', 'SK'));
define('TORET_ZASILKOVNA_COD_METHODS', array('dobirka', 'cod', 'id_codfee', 'dropshipping'));


/**
 * Vendor autoload
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/toret/toret-library/src/LibraryManager.php';
new LibraryManager();

/**
 * Log function
 *
 * @param array $data
 * @return void
 */
$ToretZasilkovnaLog = new Log(WOOZASILKOVNASLUG, TORETZASILKOVNALOGTABLE, TORETZASILKOVNALOGTABLEVERSION);
function zasilkovna_log(array $data)
{
    if (!isset($zasilkovna_option['disableLog']) || ($zasilkovna_option['disableLog'] != 'ok')) {
        $ToretZasilkovnaLog = new Log(WOOZASILKOVNASLUG, TORETZASILKOVNALOGTABLE, TORETZASILKOVNALOGTABLEVERSION);
        $ToretZasilkovnaLog->saveLog($data);
    }
}

/*
 * Check if Woo is installed
 */
if (function_exists('is_multisite') && is_multisite()) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        return;
    }
} else {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        return;
    }
}

//Load notice class
if (is_admin()) {
    require_once(TORETINCLIB . 'ToretZasilkovnaNotices.php');
    $notices = new ToretZasilkovnaNotices();
}


/*----------------------------------------------------------------------------*
* Update checker, compatibility
*----------------------------------------------------------------------------*/
require_once(WOOZASILKOVNADIR . 'includes/plugin-update-checker-master/plugin-update-checker.php');
$MyUpdateChecker = PucFactory::buildUpdateChecker(
    'http://update.toret.cz/wp-update-server-master/?action=get_metadata&slug=toret-zasilkovna',
    __FILE__,
    'toret-zasilkovna'
);

/**
 * Load the plugin text domain for translation.
 */
function zasilkovna_load_textdomain()
{
    load_plugin_textdomain('zasilkovna', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_action('init', 'zasilkovna_load_textdomain');

/*----------------------------------------------------------------------------*
* HPOS
*----------------------------------------------------------------------------*/
require_once(plugin_dir_path(__FILE__) . 'includes/class-toret-hpos-compatibility.php');
add_action('before_woocommerce_init', function () {
    if (class_exists(FeaturesUtil::class)) {
        FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});


require_once(WOOZASILKOVNADIR . 'includes/ToretZasilkovnaLib/toret-zasilkovna-functions.php');
require_once(TORETINCLIB . 'ToretZasilkovnaSettings.php');
require_once(TORETINCLIB . 'ToretZasilkovnaPUC.php');
include(TORETINCLIB . 'ToretZasilkovnaLib.php');
include(TORETINCLIB . 'ToretZasilkovnaDraw.php');
include(TORETINCEXT . 'ToretZasilkovnaFee.php');
include(TORETINCLIB . 'ToretZasilkovnaLog.php');
include(TORETINCLIB . 'ToretZasilkovnaCron.php');
include(TORETINCLIB . 'ToretZasilkovnaLimits.php');
include(TORETINCEXT . 'ToretZasilkovnaGatewayDobirka.php');
include(TORETINCEXT . 'ToretZasilkovnaPlatbaNaUcet.php');

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once(WOOZASILKOVNADIR . 'public/class-zasilkovna.php');

register_activation_hook(__FILE__, array('Toret_Zasilkovna', 'activate'));
register_deactivation_hook(__FILE__, array('Toret_Zasilkovna', 'deactivate'));

add_action('plugins_loaded', array('Toret_Zasilkovna', 'get_instance'));


/**
 * Scripts and styles
 */
add_action('admin_enqueue_scripts', 'zasilkovna_enqueue_admin_scripts');
function zasilkovna_enqueue_admin_scripts($hook_suffix)
{
    global $post;

    if (((strpos($hook_suffix, 'toret') !== false) && (strpos($hook_suffix, 'zasilkovna') !== false))
        || (strpos($hook_suffix, 'post.php') !== false)
        || $hook_suffix == 'woocommerce_page_wc-orders'
        || (isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'product_cat')) {
        wp_enqueue_script('zasilkovna-admin', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), TORETZASILKOVNAVERSION);

        wp_enqueue_script('zasilkovna-widget', TORET_ZASILKOVNA_PICKUP_WIDGET_URL, array('jquery'), TORETZASILKOVNAVERSION, ['strategy' => 'async']);

        wp_localize_script('zasilkovna-admin', 'zasilkovna_admin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'homeurl' => get_bloginfo('url'),
            'delete' => __('Delete', WOOZASILKOVNASLUG),
            'multiple_packages_label' => ToretZasilkovnaHelper::getThresholdLabels(),
        ));

    }

    wp_enqueue_style(WOOZASILKOVNASLUG . '-admin-styles', WOOZASILKOVNAURL . 'assets/css/admin.css', array(), TORETZASILKOVNAVERSION);

    if ('edit.php' == $hook_suffix) {
        if ($post) {
            if ('shop_order' === $post->post_type) {
                wp_enqueue_script('toret_admin_script', WOOZASILKOVNAURL . 'assets/js/toret-order.js', array(), TORETZASILKOVNAVERSION);
            }
        }
    }
    if ('post.php' == $hook_suffix) {
        if ($post) {
            if ('shop_order' === $post->post_type) {
                wp_enqueue_script('toret_admin_script', WOOZASILKOVNAURL . 'assets/js/toret-order.js', array(), TORETZASILKOVNAVERSION);
            }
        }
    }
    if ($hook_suffix == 'woocommerce_page_wc-orders') {
        wp_enqueue_script('toret_admin_script', WOOZASILKOVNAURL . 'assets/js/toret-order.js', array(), TORETZASILKOVNAVERSION);
    }

    if (is_admin()) {
        $screen = get_current_screen();
        if ($screen->id == 'toret-plugins_page_zasilkovna') {
            wp_enqueue_script('towp-draw-select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'));
            wp_enqueue_style('towp-draw-select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');

        }
    }

}

add_action('wp_enqueue_scripts', 'zasilkovna_enqueue_styles');
function zasilkovna_enqueue_styles()
{
    wp_enqueue_style('zasilkovna-public-styles', plugins_url('assets/css/public.css', __FILE__), array(), TORETZASILKOVNAVERSION);
}

add_action('wp_enqueue_scripts', 'zasilkovna_enqueue_scripts');
function zasilkovna_enqueue_scripts()
{
    wp_enqueue_script('zasilkovna-public', plugins_url('assets/js/public.js', __FILE__), array('jquery'));

    $zasilkovna_option = get_option('zasilkovna_option');
    $apiKey = $zasilkovna_option['api_key'];

    // Přidáme ajaxurl do skriptu
    wp_localize_script('zasilkovnaAjax', 'zasilkovnaAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'apikey' => $apiKey,
        'appIdentity' => WOOZASILKOVNAVER,
    ));

    $load_in_enqueue = apply_filters('zasilkovna_load_in_enqueue', true);
    if ($load_in_enqueue && tzas_better_is_checkout()) {
        wp_enqueue_script('zasilkovna-widget', TORET_ZASILKOVNA_PICKUP_WIDGET_URL, array('jquery'), TORETZASILKOVNAVERSION, ['strategy' => 'async']);
        wp_enqueue_script('zasilkovna-hd-widget', TORET_ZASILKOVNA_HD_WIDGET_URL, array('jquery'), TORETZASILKOVNAVERSION, ['strategy' => 'async']);
    }
}

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/
if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
    require_once(WOOZASILKOVNADIR . 'admin/class-zasilkovna-admin.php');
    add_action('plugins_loaded', array('Toret_Zasilkovna_Admin', 'get_instance'));
    include(WOOZASILKOVNADIR . 'admin/includes/class-admin-save.php');
    include(WOOZASILKOVNADIR . 'admin/includes/class-zasilkovna-columns.php');
    include(WOOZASILKOVNADIR . 'admin/includes/class-zasilkovna-product-tab.php');
    include(WOOZASILKOVNADIR . 'admin/includes/class-zasilkovna-bulk.php');
    include(WOOZASILKOVNADIR . 'admin/includes/class-zasilkovna-send-ticket.php');

    add_action('admin_enqueue_scripts', function () {
        if (is_admin()) {
            wp_enqueue_media();
        }
    });
}

add_filter('woocommerce_email_classes', 'add_zasilkovna_error_woocommerce_email');
function add_zasilkovna_error_woocommerce_email($email_classes)
{
    require_once(TORETINCEXT . 'ToretZasilkovnaWcAdminErrorInfo.php');
    $email_classes['WC_Zasilkovna_Admin_Error_Info'] = new ToretZasilkovnaWcAdminErrorInfo();
    return $email_classes;
}

/**
 * Custom endpoint
 */
add_action('init', 'zasikovna_add_json_endpoint');
function zasikovna_add_json_endpoint()
{
    add_rewrite_endpoint('zasilkovna', EP_ALL);
}

/**
 * Get cod value
 */
function zasilkovna_get_cod_value($s_method, $price, $country, $order)
{
    if (!empty($s_method)) {

        $zasilkovna_option = get_option('zasilkovna_option', array());

        $dobirka_id = apply_filters('zasilkovna_dobirka_shipping_id', TORET_ZASILKOVNA_COD_METHODS, $s_method, $price, $country);

        if (in_array($s_method, $dobirka_id)) {
            if ($country == 'CZ') {
                $cod = (int)$price;
            } else {
                $cod = (float)$price;
            }
        } else {
            $cod = 0;
        }
    } else {
        $cod = 0;
    }

    if ($country == 'HU') {
        $hu_round = $zasilkovna_option['cod_round_hu'] ?? "";
        if ($hu_round == 'ok') {
            $cod = (int)tzas_round_to_nearest_multiple($cod, 5);
        }
    }

    return apply_filters('zasilkovna_dobirka_shipping_value', $cod, $price, $country, $order);
}

/**
 * Calculate fee
 */
add_action('woocommerce_cart_calculate_fees', 'calculate_zasilkovna_fee', 10);
function calculate_zasilkovna_fee()
{
    $fee = new ToretZasilkovnaFee();
    $fee->calculate_fee();
}

/**
 * Save order info AJAX
 */
add_action('wp_ajax_toret_save_info', 'toret_save_info');
function toret_save_info()
{
    $post_id = sanitize_text_field($_POST['post_id']);

    if ((!empty($_POST['sirka'])) && (!empty($_POST['vyska'])) && (!empty($_POST['delka']))) {
        $sirka = $_POST['sirka'];
        $vyska = $_POST['vyska'];
        $delka = $_POST['delka'];
        Toret_HPOS_Compatibility::update_order_meta($post_id, 'zasilkovna_custom_dimension', $sirka . '|' . $vyska . '|' . $delka);
    }

    if (!empty($_POST['vaha'])) {
        $vaha = $_POST['vaha'];
        Toret_HPOS_Compatibility::update_order_meta($post_id, 'zasilkovna_custom_weight', $vaha);
    }

    if (!empty($_POST['total'])) {
        $total = $_POST['total'];
        Toret_HPOS_Compatibility::update_order_meta($post_id, 'zasilkovna_custom_total', $total);
    }

    echo 'ok';
    exit();
}


/**
 * Save order info AJAX
 */
add_action('wp_ajax_toret_save_customs_info', 'toret_save_customs_info');
function toret_save_customs_info()
{
    $post_id = sanitize_text_field($_POST['post_id']);

    if (!empty($_POST['date'])) {
        Toret_HPOS_Compatibility::update_order_meta($post_id, 'tzas-invoice-date', $_POST['date']);
    }

    if (!empty($_POST['number'])) {
        Toret_HPOS_Compatibility::update_order_meta($post_id, 'tzas-invoice-number', $_POST['number']);
    }

    echo 'ok';
    exit();
}

/**
 * Delete package AJAX
 */
add_action('wp_ajax_zasilkovna_delete', 'zasilkovna_delete');
add_action('wp_ajax_nopriv_zasilkovna_delete', 'zasilkovna_delete');
function zasilkovna_delete()
{
    $ToretZasilkovna = ToretZasilkovnaLib();
    $ToretZasilkovna->Helper->clear_all_package_data($_POST['orderid']);
    wp_die();
}

/**
 * Cancel package AJAX
 */
add_action('wp_ajax_zasilkovna_cancel_package', 'zasilkovna_cancel_package');
add_action('wp_ajax_nopriv_zasilkovna_cancel_package', 'zasilkovna_cancel_package');
function zasilkovna_cancel_package()
{
    $orderid = $_POST['orderid'];
    $claim = $_POST['claim'];
    packetka_cancel_package($orderid, $claim);
}

function packetka_cancel_package($order_id, $claim, $ajax = true)
{
    $zasilkovna_option = get_option('zasilkovna_option');
    $apiPassword = $zasilkovna_option['api_password'];

    $zasilkovna_id_zasilky_claim = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_assistent', true);
    $zasilkovna_id_zasilky = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky', true);

    $zasilkovna_claim_ids = explode(';', $zasilkovna_id_zasilky_claim);
    $zasilkovna_ids = explode(';', $zasilkovna_id_zasilky);

    if ($claim == 'yes') {

        foreach ($zasilkovna_claim_ids as $zasilkovna_claim_id) {

            if (empty($zasilkovna_claim_id)) {
                continue;
            }

            try {
                $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
                $gw->__soapCall('cancelPacket', array($apiPassword, 'id' => (string)$zasilkovna_claim_id));
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_claim_status');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_claim_status');
            } catch (SoapFault $e) {
                $error = key($e->detail) ?? $e->faultstring;
                zasilkovna_log(array(
                    'order_id' => $order_id,
                    'log' => __('Package id: ', TORETZASILKOVNASLUG) . $zasilkovna_claim_id,
                    'context' => __('Failed to cancel package. Error: ', 'zasilkovna') . $error
                ));
            }
        }

    } else {

        foreach ($zasilkovna_ids as $zasilkovna_id) {
            if (empty($zasilkovna_id)) {
                continue;
            }
            try {
                $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
                $gw->__soapCall('cancelPacket', array($apiPassword, 'id' => (string)$zasilkovna_id));
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_status');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_status');
            } catch (SoapFault $e) {
                $error = key($e->detail) ?? $e->faultstring;
                zasilkovna_log(array(
                    'order_id' => $order_id,
                    'log' => __('Package id: ', TORETZASILKOVNASLUG) . $zasilkovna_id,
                    'context' => __('Failed to cancel package. Error: ', 'zasilkovna') . $error
                ));
            }
        }

        foreach ($zasilkovna_claim_ids as $zasilkovna_claim_id) {
            if (empty($zasilkovna_claim_id)) {
                continue;
            }

            try {

                $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
                $gw->__soapCall('cancelPacket', array($apiPassword, 'id' => (string)$zasilkovna_claim_id));
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_claim_status');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage_assistent');
                Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_claim_status');

            } catch (SoapFault $e) {
                $error = key($e->detail) ?? $e->faultstring;
                zasilkovna_log(array(
                    'order_id' => $order_id,
                    'log' => __('Package id: ', TORETZASILKOVNASLUG) . $zasilkovna_claim_id,
                    'context' => __('Failed to cancel package. Error: ', 'zasilkovna') . $error
                ));
            }
        }
    }

    if ($ajax) {
        wp_die('packetkacancelfinished');
    }
}

/**
 * Get shipping key AJAX
 */
add_action('wp_ajax_toret_get_key', 'toret_get_key');
add_action('wp_ajax_nopriv_toret_get_key', 'toret_get_key');
function toret_get_key()
{
    add_filter('weglot_type_treat_page', function ($type) {
        return 'ajax';
    });

    $slug = tzas_get_service_from_string($_POST['nazev']);

    $ToretZasilkovna = ToretZasilkovnaLib();

    if (tzas_is_native_zpoint_method($slug)) {
        wp_die('packeta-zpoints');
    }

    if (tzas_is_native_zbox_method($slug)) {
        wp_die('packeta-zbox');
    }

    if ($slug) {
        $data = $ToretZasilkovna->Helper->GetServiceBySlug($slug);
        if (isset($data['key'])) {
            wp_die($data['key']);
        }
    }

    wp_die('packeta');
}

/**
 * Get selected point AJAX
 */
add_action('wp_ajax_vybrana_pobocka', 'vybrana_pobocka');
add_action('wp_ajax_nopriv_vybrana_pobocka', 'vybrana_pobocka');
function vybrana_pobocka()
{
    $PacketaData = array();

    WC()->session->__unset('PacketaPointData');
    WC()->session->__unset('zasilkovna_creditCardPayment');

    if (isset($_POST['point']['id'])) {
        $PacketaData['zasilkovna_id_pobocky'] = esc_attr($_POST['point']['id']);
        $PacketaData['zasilkovna_id'] = esc_attr($_POST['point']['id']);
    }

    if (isset($_POST['point']['carrierId'])) {
        $PacketaData['zasilkovna_carrierId'] = esc_attr($_POST['point']['carrierId']);
    }

    if (isset($_POST['point']['carrierPickupPointId'])) {
        $PacketaData['zasilkovna_carrierPickupPointId'] = esc_attr($_POST['point']['carrierPickupPointId']);
    }

    if (isset($_POST['point']['name'])) {
        $PacketaData['zasilkovna_name'] = esc_attr($_POST['point']['name']);
    }

    if (isset($_POST['point']['country'])) {
        $PacketaData['zasilkovna_country'] = esc_attr($_POST['point']['country']);
    }

    if (isset($_POST['point']['place'])) {
        $PacketaData['zasilkovna_place'] = esc_attr($_POST['point']['place']);
    }

    if (isset($_POST['point']['street'])) {
        $PacketaData['zasilkovna_street'] = esc_attr($_POST['point']['street']);
    }

    if (isset($_POST['point']['city'])) {
        $PacketaData['zasilkovna_city'] = esc_attr($_POST['point']['city']);
    }

    if (isset($_POST['point']['zip'])) {
        $PacketaData['zasilkovna_zip'] = esc_attr($_POST['point']['zip']);
    }

    if (isset($_POST['point']['url'])) {
        $PacketaData['zasilkovna_url'] = esc_attr($_POST['point']['url']);
    }

    if (isset($_POST['point']['group'])) {
        $creditCardPayment = 'true';
        if ($_POST['point']['group'] == 'zbox') {
            $creditCardPayment = 'false';
        }
        WC()->session->set('zasilkovna_creditCardPayment', $creditCardPayment);
    } else {
        WC()->session->set('zasilkovna_creditCardPayment', "true");
    }

    if (!empty($PacketaData)) {
        WC()->session->set('PacketaPointData', $PacketaData);
    }

    exit();
}

add_action('wp_ajax_vybrana_adresa', 'tzas_vybrana_adresa');
add_action('wp_ajax_nopriv_vybrana_adresa', 'tzas_vybrana_adresa');
function tzas_vybrana_adresa()
{
    if (isset($_POST['vybrana_adresa'])) {
        WC()->session->set('addressSelected', true);
    }
    exit();
}

add_action('wp_ajax_reset_vybrana_adresa', 'tzas_reset_vybrana_adresa');
add_action('wp_ajax_nopriv_reset_vybrana_adresa', 'tzas_reset_vybrana_adresa');
function tzas_reset_vybrana_adresa()
{
    if (isset($_POST['reset_vybrana_adresa'])) {
        WC()->session->set('addressSelected', false);
        WC()->session->__unset('addressSelected');
    }
    exit();
}

/**
 * Change selected point AJAX
 */
add_action('wp_ajax_zmenit_pobocku', 'zmenit_pobocku');
function zmenit_pobocku()
{
    $order_id = $_POST['id'];

    if (isset($_POST['point']['id'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_pobocky', esc_attr($_POST['point']['id']));
    }

    if (isset($_POST['point']['carrierId'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_carrierId', esc_attr($_POST['point']['carrierId']));
    }

    if (isset($_POST['point']['carrierPickupPointId'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_carrierPickupPointId', esc_attr($_POST['point']['carrierPickupPointId']));
    }

    if (isset($_POST['point']['name'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_name', esc_attr($_POST['point']['name']));
    }

    if (isset($_POST['point']['country'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_country', esc_attr($_POST['point']['country']));
    }

    if (isset($_POST['point']['place'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_place', esc_attr($_POST['point']['place']));
    }

    if (isset($_POST['point']['street'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_street', esc_attr($_POST['point']['street']));
    }

    if (isset($_POST['point']['city'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_city', esc_attr($_POST['point']['city']));
    }

    if (isset($_POST['point']['zip'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_zip', esc_attr($_POST['point']['zip']));
    }

    if (isset($_POST['point']['url'])) {
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_url', esc_attr($_POST['point']['url']));
    }
    exit();
}

/**
 * Change shipping AJAX
 */
add_action('wp_ajax_toret_zasilkovna_change_shipping', 'toret_zasilkovna_change_shipping');
function toret_zasilkovna_change_shipping()
{
    $order_id = $_POST['orderid'];
    $method = $_POST['method'];
    $country = strtolower($_POST['country']);

    $order = wc_get_order($order_id);

    if (empty($method))
        return;

    $method_id = tzas_get_shipping_base_from_string($method);
    $service_slug = tzas_get_service_from_string($method);

    $ToretZasilkovna = ToretZasilkovnaLib();
    $service_data = $ToretZasilkovna->Helper->GetServiceBySlug($service_slug);

    $subtotal = $order->get_subtotal();

    $delete_point = false;

    if (!tzas_is_native_method($service_slug)) {

        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_pobocky', $service_data['key']);
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_dopravy', $method);

        if ($service_data['pobocky'] == '0') {
            $delete_point = true;
        }

        $weight = (new ToretZasilkovnaDimensionHelper)->get_order_total_weight($order);
        $weight = apply_filters('zasilkovna_packeta_weight', $weight);
        $max_dim = (new ToretZasilkovnaDimensionHelper)->get_max_dimension($order->get_items());

        $zasilkovna_option = get_option('zasilkovna_option');
        $zasilkovna_prices = get_option('zasilkovna_prices', array());
        $cost = $ToretZasilkovna->Helper->GetServiceCost($zasilkovna_option, $zasilkovna_prices, $country, $weight, $max_dim, $subtotal, $service_data, $order->get_items());


        $cost = $ToretZasilkovna->Helper->currency_compatibility($cost);
        $cost = apply_filters($service_data['slug'] . '_shipping_cost', $cost, $country, $weight);

        if ($cost != -1) {

            if ($ToretZasilkovna->Helper->CheckIfForFree($service_data['slug'], $country, $order->get_items())) {
                $cost = 0;
            }

            if (empty($cost))
                $cost = 0;

            $cost = $ToretZasilkovna->Helper->ServiceFreeShipping($zasilkovna_option, $zasilkovna_prices, $cost, $service_data, $subtotal, 0);
            $cost = apply_filters($service_data['slug'] . '_shipping_cost_free', $cost, $country, $weight);
            $zasilkovna_services = get_option('zasilkovna_services');
            $label = $ToretZasilkovna->Helper->ServiceLabel($zasilkovna_services, $cost, $service_data['key']);
        }

    } else {

        $weight = (new ToretZasilkovnaDimensionHelper)->get_order_total_weight($order);
        $weight = apply_filters('zasilkovna_packeta_weight', $weight);
        $max_dim = (new ToretZasilkovnaDimensionHelper)->get_max_dimension($order->get_items());

        $zasilkovna_option = get_option('zasilkovna_option', array());
        $zasilkovna_prices = get_option('zasilkovna_prices', array());
        $cost = $ToretZasilkovna->Helper->GetPacketaShippingPrice($service_slug, $zasilkovna_option, $zasilkovna_prices, $country, $weight, $max_dim, $subtotal, $order->get_items());

        $cost = $ToretZasilkovna->Helper->currency_compatibility($cost);
        $cost = apply_filters('zasilkovna_shipping_cost', $cost, $country, $weight);

        if ($cost != -1) {

            if ($ToretZasilkovna->Helper->CheckIfForFree($service_slug, $country, $order->get_items())) {
                $cost = 0;
            }

            $cost = $ToretZasilkovna->Helper->PacketaFreeShipping($service_slug, $zasilkovna_prices, $cost, $country, $subtotal, 0);
            $cost = apply_filters('zasilkovna_shipping_cost_free', $cost, $country, $weight);
            $label = $ToretZasilkovna->Helper->PacketaLabel($cost, $country, $service_slug);

            Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_pobocky', '');
            Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_dopravy', $method);

        }
    }

    if ($cost == -1) {
        exit();
    }

    $calculate_tax_for = [
        'country' => $order->get_shipping_country(),
        'state' => $order->get_shipping_state(),
        'postcode' => $order->get_shipping_postcode(),
        'city' => $order->get_shipping_city(),
    ];

    $changed = false;

    $shipping_items = $order->get_items('shipping');
    if (!empty($shipping_items)) {
        foreach ($order->get_items('shipping') as $item_id => $item) {
            $shipping_zone = WC_Shipping_Zones::get_zone_by('instance_id', $item->get_instance_id());
            $shipping_methods = $shipping_zone->get_shipping_methods();
            foreach ($shipping_methods as $instance_id => $shipping_method) {
                if ($shipping_method->is_enabled() && $shipping_method->id === $method_id) {
                    $item->set_method_title($shipping_method->get_title());
                    $item->set_method_id($shipping_method->get_rate_id());
                    $item->set_total($cost);
                    $item->calculate_taxes($calculate_tax_for);
                    $item->save();
                    $changed = true;
                    break;
                }
            }
        }
    } else {

        $country_code = strtoupper($country);
        $defined_zones = WC_Shipping_Zones::get_zones();
        $shipping_methods = array();
        $country_found = false;

        foreach ($defined_zones as $zone) {
            foreach ($zone['zone_locations'] as $location) {
                if ('country' === $location->type && $country_code === $location->code) {
                    $shipping_methods = array_merge($shipping_methods, $zone['shipping_methods']);
                    $country_found = true;
                    break;
                }
            }
        }

        if (!$country_found) {
            $zone = new \WC_Shipping_Zone(0);
            $shipping_methods = array_merge($shipping_methods, $zone->get_shipping_methods(true));
        }

        foreach ($shipping_methods as $instance_id => $shipping_method) {
            if ($shipping_method->is_enabled() && $shipping_method->id === $method_id) {
                $item = new WC_Order_Item_Shipping();
                $item->set_method_title($label);
                $item->set_method_id($shipping_method->get_rate_id());
                $item->set_total($cost);
                $item->calculate_taxes($calculate_tax_for);
                $item->save();
                $order->add_item($item);
                $changed = true;
                break;
            }
        }
    }

    if ($changed) {
        if (!empty($country)) {
            $order->set_billing_country(strtoupper($country));
            $order->set_shipping_country(strtoupper($country));
        }

        $order->calculate_shipping();
        $order->calculate_totals(); // the save() method is included
        Toret_HPOS_Compatibility::update_order_meta($order_id, "zasilkovna_shipping_update", '');

        if ($delete_point) {
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_name");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_place");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_gps_lat");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_gps_lon");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_street");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_city");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_country");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_zip");
            Toret_HPOS_Compatibility::delete_order_meta($order_id, "zasilkovna_url");
        }
    }

    exit();
}

add_action('woocommerce_checkout_update_order_review', 'toret_checkout_update_refresh_shipping_methods', 10, 1);
function toret_checkout_update_refresh_shipping_methods()
{
    $packages = WC()->cart->get_shipping_packages();
    foreach ($packages as $package_key => $package) {
        WC()->session->set('shipping_for_package_' . $package_key, false);
    }
}

add_action('init', 'toret_add_endpoint');
if (!function_exists('toret_add_endpoint')) {
    function toret_add_endpoint()
    {
        add_rewrite_endpoint('zasilkovnasendticket', EP_ALL);
        add_rewrite_endpoint('zasilkovnabulkprint', EP_ALL);
        add_rewrite_endpoint('zasilkovnabulksend', EP_ALL);
        add_rewrite_endpoint('packetaservices', EP_ALL);
        add_rewrite_endpoint('packetkastatus', EP_ALL);
    }
}

/**
 * Add template redirect
 */
add_action('template_redirect', 'toret_template_redirect');
if (!function_exists('toret_template_redirect')) {
    function toret_template_redirect()
    {
        global $wp_query;

        if (!isset($wp_query->query_vars['zasilkovnasendticket']) && !isset($wp_query->query_vars['zasilkovnabulkprint']) && !isset($wp_query->query_vars['zasilkovnabulksend'])) {
            return;
        }

        if (isset($wp_query->query_vars['zasilkovnabulkprint']) && $wp_query->query_vars['zasilkovnabulkprint'] == 'app') {
            if (isset($_POST['order_ids'])) {
                $ret = ToretZasilkovnaLib()->Send->bulk_print_from_app($_POST['order_ids'], $_POST['format'] ?? "A7 on A7");
                echo $ret;
                exit();
            }
        } elseif (isset($wp_query->query_vars['zasilkovnabulksend']) && $wp_query->query_vars['zasilkovnabulksend'] == 'app') {
            if (isset($_POST['order_ids'])) {
                $return = ToretZasilkovnaLib()->Send->bulk_send_from_app($_POST['order_ids']);
                $return['response'] = 'ok';
                echo json_encode($return);
                exit();
            }
        } elseif (isset($wp_query->query_vars['zasilkovnasendticket']) && $wp_query->query_vars['zasilkovnasendticket'] == 'send') {

            if (isset($_POST['order_id'])) {
                $post_id = sanitize_text_field($_POST['order_id']);

                $ToretZasilkovna = ToretZasilkovnaLib();

                $vaha = ToretZasilkovnaDimensionHelper::get_zasilkovna_weight($post_id);

                $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_id_dopravy', true);
                $service = tzas_get_service_from_string($zasilkovna_shipping);

                $rozmery = 0;
                $shippingID = '';
                $rozmery_data = '';
                $deklarace = 'ano';

                if ($zasilkovna_shipping) {

                    $komplet_data = $ToretZasilkovna->Helper->komplet_data();
                    if (tzas_is_native_method($service)) {
                        $shippingID = (Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_carrierId', true) != 'undefined' ? Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_carrierId', true) : 0);
                    }

                    if ($shippingID != '') {
                        if ($shippingID == 0) {
                            foreach ($komplet_data as $data) {
                                if ($data['prac'] == $zasilkovna_shipping) {
                                    $rozmery = $data['rozmery'];
                                    if ($data['deklarace'] != 1) {
                                        $deklarace = 'ne';
                                    }
                                }
                            }
                        } else {
                            $service = $ToretZasilkovna->Helper->GetServiceByID($shippingID);
                            $rozmery = $service['rozmery'];
                            if ($service['deklarace'] != 1) {
                                $deklarace = 'ne';
                            }
                        }
                        if ($rozmery > 0) {
                            $rozmery_data = (Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_custom_dimension') ? Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_custom_dimension', true) : '');
                        }
                    }

                }

                if ($deklarace == 'ne') {
                    if ($rozmery == 0) {
                        if ($vaha <= 0) {
                            echo 'Weight missing';
                        } else {
                            ToretZasilkovnaLib()->Send->send_ticket($_POST['order_id'], 1);
                            echo('Successfully sended' . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_zasilky', true) . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_zasilky_dopravce', true) . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_dopravy', true));
                        }
                    } else {
                        if ($rozmery_data == '') {
                            echo 'Dimensions missing';
                        } elseif ($vaha <= 0) {
                            echo 'Weight missing';
                        } else {
                            ToretZasilkovnaLib()->Send->send_ticket($_POST['order_id'], 1);
                            echo('Successfully sended' . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_zasilky', true) . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_zasilky_dopravce', true) . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_dopravy', true));
                        }
                    }
                } else {
                    ToretZasilkovnaLib()->Send->send_ticket($_POST['order_id'], 1);
                    echo('Successfully sended' . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_zasilky', true) . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_zasilky_dopravce', true) . ';' . Toret_HPOS_Compatibility::get_order_meta($_POST['order_id'], 'zasilkovna_id_dopravy', true));

                }

            } else {
                echo 'Order Id missing';
            }
            exit();
        }
        exit();
    }
}

/**
 * Add template redirect
 */
add_action('template_redirect', 'toret_template_redirect_services');
function toret_template_redirect_services()
{
    global $wp_query;

    if (!isset($wp_query->query_vars['packetaservices'])) {
        return;
    }

    if ($wp_query->query_vars['packetaservices'] == 'run') {
        ToretZasilkovnaCron()->update_services(true);
        exit();
    }

    exit();
}


/**
 * Add template redirect
 */
add_action('template_redirect', 'toret_template_redirect_status');
function toret_template_redirect_status()
{
    global $wp_query;

    if (!isset($wp_query->query_vars['packetkastatus'])) {
        return;
    }

    if ($wp_query->query_vars['packetkastatus'] == 'run') {
        ToretZasilkovnaCron()->update_statuses(true);
        exit();
    }

    exit();
}

add_action('rest_api_init', function () {
    register_rest_route('toret-zasilkovna/v1', '/statuses', [
        'methods' => 'GET',
        'callback' => function () {
            ToretZasilkovnaCron()->update_statuses();
            return rest_ensure_response(['status' => 'ok']);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('toret-zasilkovna/v1', '/services', [
        'methods' => 'GET',
        'callback' => function () {
            ToretZasilkovnaCron()->update_services();
            return rest_ensure_response(['status' => 'ok']);
        },
        'permission_callback' => '__return_true',
    ]);
});

/*
 * Print label popup
 */
function toret_bulk_popup_hpos($order_type)
{
    global $pagenow;

    if ('shop_order' === $order_type && 'admin.php' === $pagenow) {

        $zasilkovna_option = get_option('zasilkovna_option');
        if (!empty($zasilkovna_option['disable_popup_print']) && $zasilkovna_option['disable_popup_print'] == 'ok') {

            echo '<input type="hidden" name="format" class="toret-formatbulk-packeta" value="' . ($zasilkovna_option['packeta_default_status'] ?? 'A6-on-A4') . '" />';
            echo '<input type="hidden" name="formats" class="toret-formatbulk-service" value="' . ($zasilkovna_option['packeta_services_default_status'] ?? 'A6-on-A4') . '" />';
            echo '<input type="hidden" name="offset" class="toret-input-offsetbulk" value="0" />';
            echo '<input type="hidden" name="popupdisable" class="popupdisable" value="ano" />';

        } else {
            echo ' <div class="toret-print-bulk toret-popup-print toret-orders-multi" style="display:none;">
                        <div class="toret-popup-inner toret-popup-print-inner">
                            <h2 class="toret-popup-title">' . __('Printing settings', 'zasilkovna') . '</h2>
                            <label for="toret-format" class="toret-popup-label">' . __('Format:', 'zasilkovna') . '
                                <select class="toret-format toret-formatbulk" data-id="bulk">
                                    <option class="bulk-packeta-only" value="A6-on-A4">' . __('labels, 1/4 A4, direct print, print on A4, 4pcs/page', 'zasilkovna') . '</option>
                                    <option class="bulk-packeta-only" value="A6-on-A6">' . __('labels, 1/4 A4, direct print, 1 pc/page', 'zasilkovna') . '</option>
                                    <option value="A7-on-A7">' . __('labels, 1/8 A4, 1 pc/page', 'zasilkovna') . '</option>
                                    <option value="A7-on-A4">' . __('labels, 1/8 A4, print on A4, 8pcs/page', 'zasilkovna') . '</option>
                                    <option value="A8-on-A8">' . __('labels, 1/16 A4, 1 pc/page', 'zasilkovna') . '</option>
                                    <option value="105x35mm-on-A4">' . __('labels, 105x35mm A4, print on A4, 16pcs/page', 'zasilkovna') . '</option>
                                </select>
                            </label>
                           <label for="toret-offset" class="toret-popup-label">' . __('Offset:', 'zasilkovna') . '<input type="number" min="0" step="1" value="0" id="toret-offset" class="toret-input-offset toret-input-offsetbulk" /></label>
                             <div class="toret-popup-print-buttons">
                            <button class="tzas-ulozit toret-popup-bulk-close" data-id="bulk">' . __('Close', 'zasilkovna') . '</button>     
                            <button class="tzas-ulozit toret-popup-bulk-save" data-id="bulk">' . __('Print', 'zasilkovna') . '</button>            
                        </div>
                        </div>
                    </div>';
            echo '<input type="hidden" name="" class="toret-default-packeta" value="' . ($zasilkovna_option['packeta_default_status'] ?? 'A6-on-A4') . '" />';
            echo '<input type="hidden" name="" class="toret-default-service" value="' . ($zasilkovna_option['packeta_services_default_status'] ?? 'A6-on-A4') . '" />';
        }
    }
}

function toret_bulk_popup()
{
    global $pagenow, $post_type;

    if ('shop_order' === $post_type && ('edit.php' === $pagenow || 'post.php' === $pagenow)) {

        $zasilkovna_option = get_option('zasilkovna_option');
        if (!empty($zasilkovna_option['disable_popup_print']) && $zasilkovna_option['disable_popup_print'] == 'ok') {
            echo '<input type="hidden" name="format" class="toret-formatbulk-packeta" value="' . ($zasilkovna_option['packeta_default_status'] ?? 'A6-on-A4') . '" />';
            echo '<input type="hidden" name="formats" class="toret-formatbulk-service" value="' . ($zasilkovna_option['packeta_services_default_status'] ?? 'A6-on-A4') . '" />';
            echo '<input type="hidden" name="offset" class="toret-input-offsetbulk" value="0" />';
            echo '<input type="hidden" name="popupdisable" class="popupdisable" value="ano" />';
        } else {
            echo ' <div class="toret-print-bulk toret-popup-print toret-orders-multi" style="display:none;">
                        <div class="toret-popup-inner toret-popup-print-inner">
                            <h2 class="toret-popup-title">' . __('Printing settings', 'zasilkovna') . '</h2>
                            <label for="toret-format" class="toret-popup-label">' . __('Format:', 'zasilkovna') . '
                                <select class="toret-format toret-formatbulk" data-id="bulk">
                                    <option class="bulk-packeta-only" value="A6-on-A4">' . __('labels, 1/4 A4, direct print, print on A4, 4pcs/page', 'zasilkovna') . '</option>
                                    <option class="bulk-packeta-only" value="A6-on-A6">' . __('labels, 1/4 A4, direct print, 1 pc/page', 'zasilkovna') . '</option>
                                    <option value="A7-on-A7">' . __('labels, 1/8 A4, 1 pc/page', 'zasilkovna') . '</option>
                                    <option value="A7-on-A4">' . __('labels, 1/8 A4, print on A4, 8pcs/page', 'zasilkovna') . '</option>
                                    <option value="A8-on-A8">' . __('labels, 1/16 A4, 1 pc/page', 'zasilkovna') . '</option>
                                    <option value="105x35mm-on-A4">' . __('labels, 105x35mm A4, print on A4, 16pcs/page', 'zasilkovna') . '</option>
                                </select>
                            </label>
                           <label for="toret-offset" class="toret-popup-label">' . __('Offset:', 'zasilkovna') . '<input type="number" min="0" step="1" value="0" id="toret-offset" class="toret-input-offset toret-input-offsetbulk" /></label>
                             <div class="toret-popup-print-buttons">
                            <button class="tzas-ulozit toret-popup-bulk-close" data-id="bulk">' . __('Close', 'zasilkovna') . '</button>     
                            <button class="tzas-ulozit toret-popup-bulk-save" data-id="bulk">' . __('Print', 'zasilkovna') . '</button>            
                        </div>        
                        </div>
                    </div>';
            echo '<input type="hidden" name="" class="toret-default-packeta" value="' . ($zasilkovna_option['packeta_default_status'] ?? 'A6-on-A4') . '" />';
            echo '<input type="hidden" name="" class="toret-default-service" value="' . ($zasilkovna_option['packeta_services_default_status'] ?? 'A6-on-A4') . '" />';
        }
    }
}

/*
 * Print label popup
 */
add_action('toret_detail_action', 'toret_detail_popup', 10, 1);
function toret_detail_popup($post_id)
{
    $id_zasilky = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_id_zasilky', true);
    $id_baliku_dopravce = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_id_zasilky_dopravce', true);

    $zasilkovna_option = get_option('zasilkovna_option');
    if (!empty($zasilkovna_option['disable_popup_print']) && $zasilkovna_option['disable_popup_print'] == 'ok') {
        if ($id_baliku_dopravce != '') {
            echo '<input type="hidden" name="formats" class="toret-format' . $post_id . '" value="' . ($zasilkovna_option['packeta_services_default_status'] ?? 'A6-on-A4') . '" />';
        } else {
            echo '<input type="hidden" name="format" class="toret-format' . $post_id . '" value="' . ($zasilkovna_option['packeta_default_status'] ?? 'A6-on-A4') . '" />';
        }
        echo '<input type="hidden" name="offset" class="toret-input-offset' . $post_id . '" value="0" />';
    } else {
        if (!empty($id_zasilky)) {
            echo '<div class="toret-print-' . $post_id . ' toret-popup-print toret-detail-print" style="display:none;">
                                <div class="toret-popup-inner toret-popup-print-inner">
                                    <h2 class="toret-popup-title">' . __('Printing settings', 'zasilkovna') . '</h2>
                                    <label for="toret-format" class="toret-popup-label">' . __('Format:', 'zasilkovna') . '
                                        <select class="toret-format toret-format' . $post_id . '" data-id="' . $post_id . '">';
            if (($id_baliku_dopravce != '') /*&& (($id_dopravy != 'zasilkovna>cz-zasilkovna-domu-hd') && ($id_dopravy != 'zasilkovna>sk-packeta-home-hd'))*/) {

                $labelPreference = $zasilkovna_option['packeta_services_default_status'] ?? '';

                echo '<option value="A6-on-A4" ' . ($labelPreference == 'A6-on-A4' ? 'selected' : '') . ' >' . __('labels, 1/4 A4, direct print, print on A4, 4pcs/page', 'zasilkovna') . '</option>
                                                  <option value="A6-on-A6" ' . ($labelPreference == 'A6-on-A6' ? 'selected' : '') . ' >' . __('labels, 1/4 A4, direct print, 1 pc/page', 'zasilkovna') . '</option>';
            } else {

                $labelPreference = $zasilkovna_option['packeta_default_status'] ?? '';

                echo '<option value="A6-on-A4" ' . ($labelPreference == 'A6-on-A4' ? 'selected' : '') . ' >' . __('labels, 1/4 A4, direct print, print on A4, 4pcs/page', 'zasilkovna') . '</option>
                                                  <option value="A6-on-A6" ' . ($labelPreference == 'A6-on-A6' ? 'selected' : '') . ' >' . __('labels, 1/4 A4, direct print, 1 pc/page', 'zasilkovna') . '</option>
                                                  <option value="A7-on-A7" ' . ($labelPreference == 'A7-on-A7' ? 'selected' : '') . ' >' . __('labels, 1/8 A4, 1 pc/page', 'zasilkovna') . '</option>
                                                  <option value="A7-on-A4" ' . ($labelPreference == 'A7-on-A4' ? 'selected' : '') . ' >' . __('labels, 1/8 A4, print on A4, 8pcs/page', 'zasilkovna') . '</option>
                                                  <option value="A8-on-A8" ' . ($labelPreference == 'A8-on-A8' ? 'selected' : '') . ' >' . __('labels, 1/16 A4, 1 pc/page', 'zasilkovna') . '</option>
                                                  <option value="105x35mm-on-A4" ' . ($labelPreference == '105x35mm-on-A4' ? 'selected' : '') . ' >' . __('labels, 105x35mm A4, print on A4, 16pcs/page', 'zasilkovna') . '</option>';
            }
            echo '</select>
                                    </label>
                                   <label for="toret-offset" class="toret-popup-label">' . __('Offset:', 'zasilkovna') . '<input type="number" min="0" step="1" value="0" id="toret-offset" class="toret-input-offset toret-input-offset' . $post_id . '" /></label>
                                     <div class="toret-popup-print-buttons">
                                    <button class="tzas-ulozit toret-popup-print-close" data-id="' . $post_id . '">' . __('Close', 'zasilkovna') . '</button> 
                                     <button class="tzas-ulozit toret-popup-print-save" data-id="' . $post_id . '">' . __('Print', 'zasilkovna') . '</button>   
                                    </div>                     
                                </div>
                            </div>';
        }
    }
}

/*
 * Woo cat and product variation custom tab data view and save
 */
if (is_admin()) {
    $zasilkovna_option = get_option('zasilkovna_option');
    if (isset($zasilkovna_option['tzas_hide_product_tabs']) && $zasilkovna_option['tzas_hide_product_tabs'] != 'ok') {
        include(WOOZASILKOVNADIR . 'admin/includes/ToretZasilkovnaAdminVariation.php');
        $zasilkovna_variation = new ToretZasilkovnaAdminVariation();

        add_action('woocommerce_product_after_variable_attributes', array(
            $zasilkovna_variation,
            'ZasilkovnaToretAddCustomFieldToVariations'
        ), 10, 3);
        add_action('woocommerce_save_product_variation', array(
            $zasilkovna_variation,
            'ZasilkovnaToretSaveCustomFieldVariations'
        ), 10, 2);

        include(WOOZASILKOVNADIR . 'admin/includes/ToretZasilkovnaProductCategory.php');
        $zasilkovna_category = new ToretZasilkovnaProductCategory();

        add_action('product_cat_add_form_fields', array(
            $zasilkovna_category,
            'wh_taxonomy_add_new_meta_field'
        ), 10, 1);
        add_action('product_cat_edit_form_fields', array(
            $zasilkovna_category,
            'wh_taxonomy_edit_meta_field'
        ), 10, 1);
        add_action('edited_product_cat', array($zasilkovna_category, 'wh_save_taxonomy_custom_meta'), 10, 1);
        add_action('create_product_cat', array($zasilkovna_category, 'wh_save_taxonomy_custom_meta'), 10, 1);
    }
}

/*
 * Ajax for customs declaration file load
 */
function toret_save_storage_file()
{
    $orderid = sanitize_text_field($_POST['orderid']);
    $fileid = sanitize_text_field($_POST['fileid']);
    $type = sanitize_text_field($_POST['type']);

    $ToretZasilkovna = ToretZasilkovnaLib();
    $ToretZasilkovna->Customs->create_storage_file($orderid, $type, $fileid);

    wp_die();
}

add_action('wp_ajax_toret_save_storage_file', 'toret_save_storage_file');

/**
 * On plugin update fixes
 */
add_action('plugins_loaded', 'toret_zasilkovna_on_update');
function toret_zasilkovna_on_update()
{
    $current_version = get_option('toret_zasilkovna_version', '6.9.3');
    if (version_compare($current_version, '6.9.4', '<')) {
        delete_option('zasilkovna_mista');
        delete_option('zasilkovna_mista_cz');
        delete_option('zasilkovna_mista_sk');
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'zasilkovna_mista');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'zasilkovna_mista_cz');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'zasilkovna_mista_sk');
        update_option('toret_zasilkovna_version', TORETZASILKOVNAVERSION);
    }
    if (version_compare($current_version, '6.3.8', '<')) {
        $zasilkovna_send = get_option('zasilkovna_send');
        if (isset($zasilkovna_send['status'])) {
            $enabledStatuses = apply_filters('toret_send_enabled_statuses', wc_get_order_statuses());
            foreach ($zasilkovna_send['status'] as $key => $setting) {
                if (in_array($setting, $enabledStatuses)) {
                    $new_value = array_search($setting, $enabledStatuses);
                    $zasilkovna_send['status'][$key] = $new_value;
                }
            }
            update_option('zasilkovna_send', $zasilkovna_send);
        }
        update_option('toret_zasilkovna_version', TORETZASILKOVNAVERSION);
    }
}

/**
 * Add single shipping methods
 */
add_action('woocommerce_loaded', function () {
    require_once WOOZASILKOVNADIR . 'includes/shipping_methods/ToretZasilkovnaShippingTemplate.php';
});


require_once 'checkout-blocks-initialize.php';

class Zasilkovna_Checkout_Block_Example
{

    public function __construct()
    {
        add_action('woocommerce_store_api_checkout_update_order_from_request', array(&$this, 'tzas_update_block_order_meta_delivery_date'), 10, 2);
    }

    public static function tzas_update_block_order_meta_delivery_date($order, $request)
    {
        $data = $request['extensions']['tzas-block-parcelshop'] ?? array();

        $pickup_data = $data['tzas_message'] ?? "";

        $order->update_meta_data('tzas-selected-branch', $data['tzas_message']);

        $order_id = $order->get_id();

        $zasilkovna_option = get_option('zasilkovna_option');

        $saveToLog = (isset($zasilkovna_option['branchLog']) && ($zasilkovna_option['branchLog'] == 'ok'));
        $logData = [
            'inside' => 'tzas_update_block_order_meta_delivery_date: ' . current_action(),
            'pickupData' => json_decode($pickup_data, true),
        ];

        $log = [
            'orderid' => $order_id,
            'context' => 'Pickup point selection',
            'type' => 4,
        ];

        if (!empty($pickup_data)) {
            $pickup_data = json_decode($pickup_data, true);

            $shipping_method = tzas_get_shipping_from_cart();
            $changeAddress = (!empty($zasilkovna_option['change_shipping_address']) && $zasilkovna_option['change_shipping_address'] == 'ok');

            $logData['changeShippingAddress'] = $zasilkovna_option['change_shipping_address'] ?? 'unknown';

            if (isset($pickup_data['city'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_city', esc_attr($pickup_data['city']));
                if ($changeAddress) {
                    $order->set_shipping_city(esc_attr($pickup_data['city']));
                }
            }

            if (isset($pickup_data['name'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_name', esc_attr($pickup_data['name']));
            }

            if (isset($pickup_data['place'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_place', esc_attr($pickup_data['place']));
                if ($changeAddress) {
                    $order->set_shipping_company(esc_attr($pickup_data['place']));
                }
            }

            Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_pobocky', esc_attr($pickup_data['id']));

            if (isset($pickup_data['street'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_street', esc_attr($pickup_data['street']));
                if ($changeAddress) {
                    $order->set_shipping_address_1(esc_attr($pickup_data['street']));
                }
            }

            if (isset($pickup_data['country'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_country', esc_attr($pickup_data['country']));
            }

            if (isset($pickup_data['zip'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_zip', esc_attr($pickup_data['zip']));
                if ($changeAddress) {
                    $order->set_shipping_postcode(esc_attr($pickup_data['zip']));
                }
            }

            if (isset($pickup_data['url'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_url', esc_attr($pickup_data['url']));
            }

            if (isset($pickup_data['carrierId'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_carrierId', esc_attr($pickup_data['carrierId']));
            }

            if (isset($pickup_data['carrierPickupPointId'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_carrierPickupPointId', esc_attr($pickup_data['carrierPickupPointId']));
            }

            if (isset($pickup_data['gps'])) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_gps_lat', $pickup_data['gps']['lat'] ?? '');
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_gps_lon', $pickup_data['gps']['lon'] ?? '');
            }

            Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_dopravy', $shipping_method);

        }

        $order->save();

        global $woocommerce;
        $weight = $woocommerce->cart->cart_contents_weight;
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_cart_weight', $weight);
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_cart_weight_units', get_option('woocommerce_weight_unit'));
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_cart_weight_kg', (new ToretZasilkovnaDimensionHelper())->get_cart_total_weight());

        $log['log'] = json_encode($logData);
        if ($saveToLog) {
            zasilkovna_log($log);
        }
    }

}

$checkout_block_example = new Zasilkovna_Checkout_Block_Example();


// Přidání AJAX akce pro získání váhy košíku
add_action('wp_ajax_get_cart_weight', 'handle_get_cart_weight');
add_action('wp_ajax_nopriv_get_cart_weight', 'handle_get_cart_weight');

function handle_get_cart_weight()
{
    if (!WC()->cart) {
        wp_die('0');
    }

    // Získáme váhu košíku a převedeme na kg pomocí WooCommerce funkce
    $weight = WC()->cart->get_cart_contents_weight();
    $weight_in_kg = wc_get_weight($weight, 'kg');

    wp_die($weight_in_kg);
}


/**
 * Get shipping key AJAX
 */
add_action('wp_ajax_get_disabled_zboxes', 'get_disabled_zboxes');
add_action('wp_ajax_nopriv_get_disabled_zboxes', 'get_disabled_zboxes');
function get_disabled_zboxes()
{
    $ToretZasilkovna = ToretZasilkovnaLib();
    $disabledZBOXes = $ToretZasilkovna->Helper->is_disabled_zboxes();

    wp_send_json(array(
        'disableZbox' => $disabledZBOXes === 'yes' ? 'yes' : 'no'
    ));
}

function tzas_better_is_checkout()
{
    $checkout_path = null;
    if (function_exists('wc_get_checkout_url')) {
        $checkout_path = wp_parse_url(wc_get_checkout_url(), PHP_URL_PATH);
    }
    $current_url_path = wp_parse_url("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);

    return (
        is_checkout() ||
        (
            $checkout_path !== null
            && $current_url_path !== null
            && trailingslashit($checkout_path) === trailingslashit($current_url_path)
        )
    );
}


/**
 * Hide internal shipping meta
 */
function tzas_hide_shipping_rate_meta_data($formatted_meta, $shipping_rate)
{
    foreach ($formatted_meta as $key => $meta) {
        if (isset($meta->key) && $meta->key === 'has_branches') {
            unset($formatted_meta[$key]);
        }
    }

    return $formatted_meta;
}

add_filter('woocommerce_order_item_get_formatted_meta_data', 'tzas_hide_shipping_rate_meta_data', 10, 2);