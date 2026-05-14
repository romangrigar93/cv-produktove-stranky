<?php

use ToretZasilkovna\Toret\Library\Log;

/**
 * @package   Toret Zasilkovna
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2016 Toret.cz
 */


class Toret_Zasilkovna_Admin
{

    /**
     * Instance of this class.
     */
    protected static ?Toret_Zasilkovna_Admin $instance = null;

    public function __construct()
    {
        $zasilkovna_columns = new Toret_Zasilkovna_Columns();
        $zasilkovna_tabs = new Toret_Zasilkovna_Product_Tabs();
        $zasilkovna_bulk = new Toret_Zasilkovna_Bulk();
        $zasilkovna_send = new Toret_Zasilkovna_Admin_Send();

        // Add the options page and menu item.
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));

        /**
         *  Output fix
         */
        add_action('admin_init', array($this, 'output_buffer'));
        add_action('toret_plugins_diag', array($this, 'toret_diag'), TORETZASILKOVNA);

        add_action('admin_init', array($zasilkovna_send, 'send_ticket'));

        $zasilkovna_option = get_option('zasilkovna_option');

        if (!isset($zasilkovna_option['tzas_hide_product_tabs']) || ($zasilkovna_option['tzas_hide_product_tabs'] != 'ok')) {
            add_filter('woocommerce_product_data_tabs', array(
                $zasilkovna_tabs,
                'add_zasilkovna_product_data_tab'
            ), 99, 1);
            add_action('woocommerce_product_data_panels', array(
                $zasilkovna_tabs,
                'add_zasilkovna_product_data_fields'
            ));
            add_action('woocommerce_process_product_meta', array(
                $zasilkovna_tabs,
                'woocommerce_zasilkovna_fields_save'
            ));
        }

        if (Toret_HPOS_Compatibility::is_wc_hpos_enabled()) {
            add_filter('manage_woocommerce_page_wc-orders_columns', array($zasilkovna_columns, 'barcode_column'), 20);
            add_action('manage_woocommerce_page_wc-orders_custom_column', array($zasilkovna_columns, 'barcode_column_display'), 20, 2);
        } else {
            add_filter('manage_edit-shop_order_columns', array($zasilkovna_columns, 'barcode_column'), 99999);
            add_action('manage_shop_order_posts_custom_column', array($zasilkovna_columns, 'barcode_column_display'), 10, 2);
        }


        // Add an action link pointing to the options page.
        add_filter('plugin_row_meta', array($this, 'add_action_links'), 10, 2);

        add_action('add_meta_boxes', array($this, 'metabox'), 10, 2);
        add_action('woocommerce_process_shop_order_meta', array($this, 'zasilkovna_save_meta_box'));

        if (Toret_HPOS_Compatibility::is_wc_hpos_enabled()) {
            add_action('woocommerce_order_list_table_restrict_manage_orders', 'toret_bulk_popup_hpos');
            add_filter('bulk_actions-woocommerce_page_wc-orders', array($zasilkovna_bulk, 'add_zasilkovna_bulk_action'), 20, 1);
            add_filter('handle_bulk_actions-woocommerce_page_wc-orders', array(
                $zasilkovna_bulk,
                'add_zasilkovna_handle_bulk_action_edit_shop_order'
            ), 10, 3);
        } else {
            add_action('restrict_manage_posts', 'toret_bulk_popup');
            add_filter('bulk_actions-edit-shop_order', array($zasilkovna_bulk, 'add_zasilkovna_bulk_action'), 20, 1);
            add_filter('handle_bulk_actions-edit-shop_order', array(
                $zasilkovna_bulk,
                'add_zasilkovna_handle_bulk_action_edit_shop_order'
            ), 10, 3);
        }
        add_action('admin_notices', array($zasilkovna_bulk, 'add_zasilkovna_bulk_action_admin_notice'), 10, 0);
    }

    /**
     * Return an instance of this class.
     *
     */
    public static function get_instance(): ?Toret_Zasilkovna_Admin
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register and enqueue admin-specific style sheet
     */
    public function enqueue_admin_styles($hook): void
    {

    }

    /**
     * Add settings action link to the plugins page
     */
    public function add_action_links(array $meta, string $file): array
    {
        if ($file == 'toret-zasilkovna/toret-zasilkovna.php') {
            $meta[] = '<a href="' . admin_url('admin.php?page=zasilkovna') . '">' . __('Settings', WOOZASILKOVNASLUG) . '</a>';
            $meta[] = '<a href="https://documentation.toret.cz/zasilkovna/" target="_blank">' . __('Documentation', WOOZASILKOVNASLUG) . '</a>';
            $meta[] = '<a href="https://toret.cz/podpora/" target="_blank">' . __('Support', WOOZASILKOVNASLUG) . '</a>';
        }

        return $meta;
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu
     */
    public function add_plugin_admin_menu(): void
    {
        if (!defined('TORETMENU')) {
            add_menu_page(
                __('Toret plugins', WOOZASILKOVNASLUG),
                __('Toret plugins', WOOZASILKOVNASLUG),
                'manage_woocommerce',
                'toret-plugins',
                array($this, 'display_toret_plugins_admin_page')
            );
            define('TORETMENU', true);
        }

        add_submenu_page(
            'toret-plugins',
            __('Packeta', WOOZASILKOVNASLUG),
            __('Packeta', WOOZASILKOVNASLUG),
            'manage_woocommerce',
            'zasilkovna',
            array($this, 'control_xml')
        );

        add_submenu_page(
            'toret-plugins',
            __('Packeta log', WOOZASILKOVNASLUG),
            __('Packeta log', WOOZASILKOVNASLUG),
            'manage_woocommerce',
            TORETZASILKOVNALOGSLUG,
            array($this, 'display_plugin_log_page')
        );
    }

    /**
     * Render the settings page for all plugins
     */
    public function display_toret_plugins_admin_page(): void
    {
        include_once('views/toret.php');
    }

    /**
     * Render the settings page for this plugin.
     */
    public function control_xml(): void
    {
        include_once('views/admin.php');
    }

    /**
     * Render the settings page for this plugin
     */
    public function display_plugin_log_page(): void
    {
        include_once('views/log.php');
    }


    /**
     * Headers allready sent fix
     */
    public function output_buffer(): void
    {
        ob_start();
    }

    /**
     * Get form
     */
    public function get_form(): string
    {
        if (!empty($_GET['form'])) {

            $form = esc_attr($_GET['form']);

            $forms = array();
            $items = tzas_get_admin_menu_items();
            foreach ($items as $element => $title) {
                $forms[$element . '-settings'] = $element;
            }

            if (in_array($form, array_keys($forms))) {
                return $forms[$form];
            } else {
                return $form;
            }

        } else {
            return 'general';
        }
    }

    /**
     * Get active
     */
    public function get_active(string $value)
    {
        if (!empty($_GET['form']) && $_GET['form'] == $value) {
            return 'active';
        }else{
            return '';
        }
    }

    /**
     * Metabox for order detail
     */
    public function metabox($post_type, $post): void
    {
        if ($post instanceof WC_Order)
            $order = wc_get_order($post->get_id());
        else
            $order = wc_get_order($post->ID);

        if (!$order) {
            return;
        }

        if (!isset($_GET['action']))
            return;

        if ($_GET['action'] != 'edit')
            return;

        $screen = wc_get_container()->get(Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';

        include('includes/metabox.php');

        add_meta_box(
            'zasilkovna_log',
            __('Packeta Log', WOOZASILKOVNASLUG),
            'order_zasilkovna_log_meta_box',
            $screen,
            'side',
            'high'
        );
    }

    /**
     * Metabox save
     */
    public function zasilkovna_save_meta_box($post_id): void
    {
        if (isset($_POST["tzas-change-method"])) {
            if ($_POST["tzas-change-method"] != '-') {
                Toret_HPOS_Compatibility::update_order_meta($post_id, "zasilkovna_shipping_update", $_POST["tzas-change-method"]);
            }
        }
        if (isset($_POST["zasilkovna-weight"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "zasilkovna_custom_weight", $_POST["zasilkovna-weight"]);
        }
        if (isset($_POST["zasilkovna-total"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "zasilkovna_custom_total", $_POST["zasilkovna-total"]);
        }
        if (isset($_POST["zasilkovna-dim-one"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "zasilkovna_custom_dim_one", $_POST["zasilkovna-dim-one"]);
        }
        if (isset($_POST["zasilkovna-dim-sum"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "zasilkovna_custom_dim_sum", $_POST["zasilkovna-dim-sum"]);
        }
        if (isset($_POST["tzas-invoice-number"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "tzas-invoice-number", $_POST["tzas-invoice-number"]);
        }
        if (isset($_POST["tzas_invoice_file_id"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "tzas_invoice_file_id", $_POST["tzas_invoice_file_id"]);
        }
        if (isset($_POST["tzas_invoice_file"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "tzas_invoice_file", $_POST["tzas_invoice_file"]);
        }
        if (isset($_POST["tzas-invoice-date"])) {
            Toret_HPOS_Compatibility::update_order_meta($post_id, "tzas-invoice-date", $_POST["tzas-invoice-date"]);
        }
        if ((!empty($_POST['zasilkovna-width'])) && (!empty($_POST['zasilkovna-height'])) && (!empty($_POST['zasilkovna-lenght']))) {
            $sirka = $_POST['zasilkovna-width'];
            $vyska = $_POST['zasilkovna-height'];
            $delka = $_POST['zasilkovna-lenght'];
            Toret_HPOS_Compatibility::update_order_meta($post_id, 'zasilkovna_custom_dimension', $sirka . '|' . $vyska . '|' . $delka);
        }
    }

    public function toret_diag()
    {
        $zasilkovna_option = get_option('zasilkovna_option', array());
        ?>
        Toret Zásilkovna<br/>
        ----------------------------<br/><br/>

        Licence: <?php echo get_option('woo-zasilkovna-licence-key', ''); ?><br/>
        Heslo API: <?php if (!empty($zasilkovna_option) && isset($zasilkovna_option['api_password']) && $zasilkovna_option['api_password'] != '') {
        echo 'ANO';
    } else {
        echo 'NE';
    } ?><br/>
        Označení: <?php if (!empty($zasilkovna_option) && isset($zasilkovna_option['nazev_eshopu']) && $zasilkovna_option['nazev_eshopu'] != '') {
        echo 'ANO';
    } else {
        echo 'NE';
    } ?><br/><br/>

        Státy: <?php if (!empty($zasilkovna_option['povolene_staty'])) {
        echo 'ANO';
    } else {
        echo 'NE';
    } ?><br/>

        <?php
    }

}//Class end
