<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class ToretZasilkovnaNotices
{

    public $plugin_name = 'Toret Zásilkovna';
    public $plugin_slug = 'zasilkovna';

    public function __construct()
    {
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Handle notices
     */
    public function admin_notices()
    {
        $this->woocommerce_exist();
        $this->woocommerce_version();
        $this->curl_exist();
        $this->soap_exist();
        $this->licence_key_exist();
    }

    /**
     * Upozornění na neexistence WooCommerce
     */
    private function woocommerce_exist()
    {
        $check = true;
        if (function_exists('is_multisite') && is_multisite()) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                $check = false;
            }
        } else {
            if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                $check = false;
            }
        }
        if ($check === false) {
            ?>
            <div class="notice error <?php echo $this->plugin_slug; ?>-woocommerce-notice">
                <h2><?php echo __($this->plugin_name, $this->plugin_slug) . ' - ' . __('notice', $this->plugin_slug); ?></h2>
                <p><?php _e('<strong>In order to the WooCommerce Packeta plugin to work, you need to install the WooCommerce plugin.</strong>', $this->plugin_slug); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Upozornění na nekompatibilní verzi WooCommerce
     */
    private function woocommerce_version()
    {
        if (function_exists('WC') && (version_compare(WC()->version, '3.0.0', '<'))) {
            ?>
            <div class="notice error <?php echo $this->plugin_slug; ?>-woocommerce-version-notice">
                <h2><?php echo __($this->plugin_name, $this->plugin_slug) . ' - ' . __('notice', $this->plugin_slug); ?></h2>
                <p><?php _e('<strong>The plugin requires WooCommerce version 5.0 and newer to work properly. The plugin should be compatible with version 4.0., but we cannot guarantee it to work properly. We recommend you to update.</strong>', $this->plugin_slug); ?></p>
            </div>

            <?php
        }
    }


    /**
     * Upozornění na neaktivní cURL
     */
    private function curl_exist()
    {

        if (!extension_loaded('curl')) {

            ?>
            <div class="notice error <?php echo $this->plugin_slug; ?>-curl-notice">
                <h2><?php echo __($this->plugin_name, $this->plugin_slug) . ' - ' . __('warning', $this->plugin_slug); ?></h2>
                <p><?php _e('<strong>The plugin requires active cURL library to work properly. Please contact your server administrator.</strong>', $this->plugin_slug); ?></p>
            </div>

            <?php
        }
    }

    /**
     * Upozornění na neaktivní Soap
     */
    private function soap_exist()
    {
        if (!extension_loaded('soap')) {
            ?>
            <div class="notice error <?php echo $this->plugin_slug; ?>-soap-notice">
                <h2><?php echo __($this->plugin_name, $this->plugin_slug) . ' - ' . __('warning', $this->plugin_slug); ?></h2>
                <p><?php _e('<strong>The plugin requires active Soap library to work properly. Please contact your server administrator.</strong>', $this->plugin_slug); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Upozornění na nezadanou licenci
     */
    private function licence_key_exist()
    {
        $licence_key = get_option('woo-zasilkovna-licence');
        if (empty($licence_key)) {
            ?>
            <div class="notice error <?php echo $this->plugin_slug; ?>-licence-notice">
                <h2><?php echo $this->plugin_name . ' - ' . __('warning', $this->plugin_slug); ?></h2>
                <p><?php echo '<strong>' . __('The plugin requires activation of the license for proper operation. Please verify the license in the ', $this->plugin_slug) . '<a href="' . admin_url() . 'admin.php?page=zasilkovna">' . __('plugin settings', $this->plugin_slug) . '</a>.</strong>'; ?></p>
            </div>
            <?php
        }
    }
}