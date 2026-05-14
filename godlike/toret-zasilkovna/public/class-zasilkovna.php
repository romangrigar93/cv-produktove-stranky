<?php

use ToretZasilkovna\Toret\Library\Dimensions;

class Toret_Zasilkovna
{
    /**
     * Instance of this class.
     */
    protected static ?Toret_Zasilkovna $instance = null;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    private function __construct()
    {
        // Activate plugin when new blog is added
        add_action('wpmu_new_blog', array($this, 'activate_new_site'));

        $licence = get_option('woo-zasilkovna-licence');
        if (!empty($licence) && ($licence != 'inactive')) {

            $zasilkovna_option = get_option('zasilkovna_option');

            //Remove all shipping methods, when free is available
            add_filter('woocommerce_package_rates', array($this, 'hide_shipping_when_free_is_available'), 10, 2);
            add_filter('woocommerce_package_rates', array($this, 'apply_free_shipping_coupon'), 10, 2);
            add_filter('woocommerce_package_rates', array($this, 'flr_rates'), 10000, 2);

            //Check default select
            add_action('wp_footer', array($this, 'zasilkovna_check_select'));
            add_action('wp_footer', array($this, 'zasilkovna_order_received_js_script'));

            //Recalculate cart
            $packetka_js_hook = apply_filters('toret_packetka_js_hook', 'woocommerce_review_order_after_order_total');
            add_action($packetka_js_hook, array($this, 'woo_print_autoload_js'));
            add_action($packetka_js_hook, array($this, 'woo_print_autoload_packetka_hd_js'));

            //Přidat info do detailu objednávky
            add_action('woocommerce_order_details_after_order_table', array(
                    $this,
                    'zasilkovna_customer_order_info'
            ));

            add_action('woocommerce_admin_order_data_after_billing_address', array(
                    $this,
                    'zasilkovna_admin_customer_order_info'
            ));

            //Add info into email
            add_action('woocommerce_email_after_order_table', array(
                    $this,
                    'zasilkovna_customer_email_info'
            ), 100, 2);


            //Add info into email
            $tracking_email_hook = $zasilkovna_option['email_tracking_email_hook'] ?? 'woocommerce_email_after_order_table';
            add_action($tracking_email_hook, array(
                    $this,
                    'zasilkovna_customer_email_tracking_info'
            ), 100, 2);

            //Zásilkovna select options
            add_action('woocommerce_review_order_after_shipping', array($this, 'zasilkovna_select_option'), 1, 2);

            //Save
            add_action('woocommerce_checkout_update_order_meta', array(
                    $this,
                    'store_pickup_field_update_order_meta'
            ), 15, 2);

            //Zkontrolovat vybranou pobočku
            add_action('woocommerce_checkout_process', array($this, 'zasilkovna_check_pobocka'));

            //Change email template dir
            add_filter('woocommerce_locate_template', array($this, 'toret_locate_template'), 10, 3);

            add_action('init', array('WC_Emails', 'init_transactional_emails'));

            // Create db table
            add_action('init', array($this, 'create_dopravci_table'));
            add_action('init', array($this, 'create_country_table'));
            add_action('init', array($this, 'on_order_status_change'));

            //Save weight
            add_action('woocommerce_checkout_update_order_meta', array($this, 'zasilkovna_add_cart_weight'));



            add_filter('woocommerce_thankyou_order_received_text', array($this, 'reset_session'), 20, 2);

            if (get_option('tzas_show_icon', '') == 'ok') {
                add_filter('woocommerce_cart_shipping_method_full_label', array(
                        $this,
                        'filter_woocommerce_cart_shipping_method_full_label'
                ), 10, 2);
            }

            if (!empty($zasilkovna_option['widget_position']) && $zasilkovna_option['widget_position'] == 'after')
                add_action('woocommerce_after_shipping_rate', array($this, 'button_below_rate'));
        }
    }

    function on_order_status_change()
    {
        $wc_get_order_statuses = wc_get_order_statuses();
        foreach ($wc_get_order_statuses as $key => $status) {
            add_action('woocommerce_order_status_' . substr($key, 3), array($this, 'send_ticket_automatic'), 5, 1);
        }
    }


    function button_below_rate($method)
    {
        if (is_cart())
            return;

        if (tzas_is_zasilkovna_shipping($method->get_method_id())) {

            $shipping_method = tzas_get_shipping_from_cart();
            $shipping_service = tzas_get_service_from_cart();

            if (!empty($shipping_method)) {

                if (!empty($shipping_service)) {

                    $current_id = tzas_get_service_from_string($method->get_id());

                    if ($current_id != $shipping_service)
                        return;

                    $country = toret_get_customer_country();

                    $ToretZasilkovna = ToretZasilkovnaLib();
                    $komplet_data = $ToretZasilkovna->Helper->get_komplet_data();

                    $pobocky = 0;
                    foreach ($komplet_data as $data) {
                        if ($data['prac'] == $shipping_method && $data['pobocky'] == 1) {
                            $pobocky = $data['pobocky'];
                        }
                    }
                    $img = '';

                    if (($pobocky == 1) || tzas_is_native_pickup_method($shipping_service)) {

                        $html = '<a class="button zas-pop-kont zas-tlac" data-native="' . tzas_get_native_slug_from_service($shipping_service) . '" style="cursor: pointer;">' . __('Select a pick-up point', 'zasilkovna') . '</a><a style="display:none;cursor: pointer" href="#" class="zas-tlac2 button packeta-selector-open">' . __('Select a pick-up point', 'zasilkovna') . '</a>';
                        echo '<div>' . $img . $html . '</div>';
                        echo '<div class="packeta-selector-branch-name method-detail toret-vyber-pobocky display_branch" data-native="' . tzas_get_native_slug_from_service($shipping_service) . '"></div>';
                    } else {
                        $zasilkovna_option = get_option('zasilkovna_option');
                        if (in_array($country, TORET_ZASILKOVNA_HD_WIDGET_COUNTRIES) && !empty($zasilkovna_option['api_key'])) {
                            if ($zasilkovna_option['enableHDChecker'] ?? '' == 'ok') {
                                $html = '<a class="button zas-pop-hd-kont zas-hd-tlac" style="cursor: pointer;">' . __('Select address', 'zasilkovna') . '</a><a style="display:none;cursor: pointer" href="#" class="zas-hd-tlac2 button packeta-selector-open">' . __('Select address', 'zasilkovna') . '</a>';
                                echo '<div>' . $img . $html . '</div>';
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Return an instance of this class.
     */
    public static function get_instance(): ?Toret_Zasilkovna
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated
     */
    public static function activate(bool $network_wide)
    {
        if (function_exists('is_multisite') && is_multisite()) {

            if ($network_wide) {

                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id) {

                    switch_to_blog($blog_id);
                    self::single_activate();
                }

                restore_current_blog();

            } else {
                self::single_activate();
            }

        } else {
            self::single_activate();
        }

    }

    /**
     * Get all blog ids of blogs in the current network
     */
    private static function get_blog_ids()
    {
        global $wpdb;

        $sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

        return $wpdb->get_col($sql);
    }

    /**
     * Fired for each blog when the plugin is activated
     */
    private static function single_activate()
    {
        if (!class_exists('WooCommerce')) {

            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('This plugin requires WooCommerce to be installed and active.', TORETZASILKOVNASLUG));

        } else {

            $woo_version = WC()->version;
            $required_version = '6.7.0';

            if (version_compare($woo_version, $required_version, '<')) {
                wp_die('Could not be activated. ' . sprintf(
                                '%1$s requires WooCommerce version %2$s or higher installed and active. You can download WooCommerce latest version %3$s OR go back to %4$s.',
                                '<strong>Toret Zasilkovna</strong>',
                                $required_version,
                                '<strong><a href="https://wordpress.org/plugins/woocommerce/">from here</a></strong>',
                                '<strong><a href="' . esc_url(admin_url('plugins.php')) . '">plugins page</a></strong>'
                        ));
            }
        }
    }

    /**
     * Fired when the plugin is deactivated
     */
    public
    static function deactivate(bool $network_wide)
    {
        if (function_exists('is_multisite') && is_multisite()) {

            if ($network_wide) {

                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id) {

                    switch_to_blog($blog_id);
                    self::single_deactivate();

                }

                restore_current_blog();

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     */
    private
    static function single_deactivate()
    {

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     */
    public
    function activate_new_site(int $blog_id)
    {
        if (1 !== did_action('wpmu_new_blog')) {
            return;
        }

        switch_to_blog($blog_id);
        self::single_activate();
        restore_current_blog();
    }

    /**
     * Remove all shipping methods, when free is available
     */
    public function hide_shipping_when_free_is_available($rates, $package)
    {
        $old_rates = $rates;

        $zasilkovna_option = get_option('zasilkovna_option');
        if (!empty($zasilkovna_option['doprava_zdarma']) && $zasilkovna_option['doprava_zdarma'] == 'default') {
            return $rates;
        }

        $free = false;
        $free_rate_id = 0;
        foreach ($rates as $rate_id => $rate) {

            if ('free_shipping' === $rate->method_id) {
                $free = true;
                $free_rate_id = $rate_id;
                break;
            }
        }

        if ($free === true) {
            if (!empty($zasilkovna_option['doprava_zdarma']) && $zasilkovna_option['doprava_zdarma'] == 'all') {
                foreach ($rates as $item) {
                    $item->cost = 0;
                    $item->tax = 0;
                    $item->taxes = false;
                }
            } elseif (!empty($zasilkovna_option['doprava_zdarma']) && $zasilkovna_option['doprava_zdarma'] == 'zasilkovna') {
                $is_zasilkovna = tzas_is_zasilkovna_shipping(tzas_get_shipping_from_cart());
                foreach ($rates as $item) {
                    if ($is_zasilkovna) {
                        $item->cost = 0;
                        $item->tax = 0;
                        $item->taxes = false;
                    }
                }
            }
            unset($rates[$free_rate_id]);
        }

        return apply_filters('zasilkovna_free_shipping_rates', $rates, $old_rates, $package);
    }

    /**
     * Check default select
     */
    public
    function zasilkovna_check_select()
    {
        if (!tzas_better_is_checkout()) {
            return;
        }

        if (WC()->cart->needs_shipping()) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {

                    var checkout_form = jQuery('form.checkout');

                    checkout_form.on('checkout_place_order_success', function () {
                        jQuery('#zasilkovna_id_zas').val("");
                        sessionStorage.removeItem('zasilkovnaPobockaName')
                        sessionStorage.removeItem('zasilkovnaVybranaPobocka')
                        sessionStorage.removeItem('zasilkovnacarrierId')
                        sessionStorage.removeItem('zasilkovnacarrierPickupPointId')
                        sessionStorage.removeItem('zasilkovnaVybranaPobockaPlace')
                        sessionStorage.removeItem('zasilkovnaVybranaPobockaGpsLat')
                        sessionStorage.removeItem('zasilkovnaVybranaPobockaGpsLon')
                        sessionStorage.removeItem('zasilkovnaVybranaPobockaStreet')
                        sessionStorage.removeItem('zasilkovnaVybranaPobockaCity')
                        sessionStorage.removeItem('zasilkovnaVybranaPobockaZip')
                        sessionStorage.removeItem('zasilkovnaVybranaPobockaURL')
                    });

                    jQuery(document.body).trigger('update_checkout');

                    <?php

                    $zasilkovna_option = get_option('zasilkovna_option');

                    if ( !empty($zasilkovna_option['checkout_point_js_check']) && $zasilkovna_option['checkout_point_js_check'] == 'ok' ) {

                    if(isset(WC()->session)){

                    $shipping_method = tzas_get_shipping_from_cart();
                    $shipping_service = tzas_get_service_from_cart();

                    if($shipping_method){

                    $ToretZasilkovna = ToretZasilkovnaLib();
                    $komplet_data = $ToretZasilkovna->Helper->komplet_data();
                    $pobocky = 0;

                    foreach ($komplet_data as $data) {
                        if ($data['prac'] == $shipping_method && $data['pobocky'] == 1) {
                            $pobocky = $data['pobocky'];
                        }
                    }

                    if ( !empty($shipping_service) && (tzas_is_native_pickup_method($shipping_service) || $pobocky == 1) ) {

                    ?>

                    jQuery('body').on('click', '#place_order', function () {
                        if (jQuery('body .zasilkovna_id').val() === 'default') {
                            alert('<?php _e('Please select a pick-up point.', 'zasilkovna'); ?>');
                            return false;
                        }
                    });

                    <?php

                    }
                    }
                    }
                    }
                    ?>

                    jQuery(document.body).on('updated_checkout', function () {
                        if (typeof sessionStorage.zasilkovnaVybranaPobocka !== "undefined") {
                            jQuery('#zasilkovna_id_zas').val(sessionStorage.zasilkovnaVybranaPobocka);
                        }

                        if (typeof sessionStorage.zasilkovnacarrierId !== "undefined") {
                            jQuery('#zasilkovna_carrierId').val(sessionStorage.zasilkovnacarrierId);
                        }

                        if (typeof sessionStorage.zasilkovnacarrierPickupPointId !== "undefined") {
                            jQuery('#zasilkovna_carrierPickupPointId').val(sessionStorage.zasilkovnacarrierPickupPointId);
                        }

                        if (typeof sessionStorage.zasilkovnaPobockaName !== "undefined") {
                            jQuery('.packeta-selector-branch-name').text(sessionStorage.zasilkovnaPobockaName);
                            jQuery('#zasilkovna_name').val(sessionStorage.zasilkovnaPobockaName);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaPlace !== "undefined") {
                            jQuery('#zasilkovna_place').val(sessionStorage.zasilkovnaVybranaPobockaPlace);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaGpsLat !== "undefined") {
                            jQuery('#zasilkovna_gps_lat').val(sessionStorage.zasilkovnaVybranaPobockaGpsLat);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaGpsLon !== "undefined") {
                            jQuery('#zasilkovna_gps_lon').val(sessionStorage.zasilkovnaVybranaPobockaGpsLon);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaStreet !== "undefined") {
                            jQuery('#zasilkovna_street').val(sessionStorage.zasilkovnaVybranaPobockaStreet);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaCity !== "undefined") {
                            jQuery('#zasilkovna_city').val(sessionStorage.zasilkovnaVybranaPobockaCity);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaCountry !== "undefined") {
                            jQuery('#zasilkovna_country').val(sessionStorage.zasilkovnaVybranaPobockaCountry);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaZip !== "undefined") {
                            jQuery('#zasilkovna_zip').val(sessionStorage.zasilkovnaVybranaPobockaZip);
                        }

                        if (typeof sessionStorage.zasilkovnaVybranaPobockaURL !== "undefined") {
                            jQuery('#zasilkovna_url').val(sessionStorage.zasilkovnaVybranaPobockaURL);
                        }

                        if (typeof sessionStorage.creditCardPayment !== "undefined") {
                            jQuery('#zasilkovna_creditCardPayment').val(sessionStorage.creditCardPayment);
                        }
                    });
                });
            </script>
            <?php
        }


    }

    function zasilkovna_order_received_js_script()
    {
        if (!is_wc_endpoint_url('order-received'))
            return;

        $order_id = absint(get_query_var('order-received'));

        if (get_post_type($order_id) !== 'shop_order') {
            return;
        }

        ?>
        <script>
            jQuery(function ($) {
                sessionStorage.removeItem('zasilkovnaPobockaName')
                sessionStorage.removeItem('zasilkovnaVybranaPobocka')
                sessionStorage.removeItem('zasilkovnacarrierId')
                sessionStorage.removeItem('zasilkovnacarrierPickupPointId')
                sessionStorage.removeItem('zasilkovnaVybranaPobockaPlace')
                sessionStorage.removeItem('zasilkovnaVybranaPobockaGpsLat')
                sessionStorage.removeItem('zasilkovnaVybranaPobockaGpsLon')
                sessionStorage.removeItem('zasilkovnaVybranaPobockaStreet')
                sessionStorage.removeItem('zasilkovnaVybranaPobockaCity')
                sessionStorage.removeItem('zasilkovnaVybranaPobockaZip')
                sessionStorage.removeItem('zasilkovnaVybranaPobockaURL')
            });
        </script>
        <?php
    }

    public
    function woo_print_autoload_packetka_hd_js()
    {
        $zasilkovna_option = get_option('zasilkovna_option');
        if (($zasilkovna_option['enableHDChecker'] ?? '') != 'ok') {
            return;
        }

        if (tzas_better_is_checkout() && empty($wp->query_vars['order-pay']) && !isset($wp->query_vars['order-received'])) {

            $country = toret_get_customer_country();

            if (!in_array($country, TORET_ZASILKOVNA_HD_WIDGET_COUNTRIES)) {
                return;
            }

            $zasilkovna_option = get_option('zasilkovna_option');

            if (empty($zasilkovna_option['api_key'])) {
                return;
            }
            $langs = explode('_', get_locale());

            $packeta_language = $langs[0];
            $packeta_country = strtolower($country);

            $zasilkovna_option = get_option('zasilkovna_option', array());

            $load_in_enqueue = apply_filters('zasilkovna_load_in_enqueue', true);
            if (!$load_in_enqueue) {
                ?>
                <script src="<?php echo TORET_ZASILKOVNA_HD_WIDGET_URL; ?>"></script>
                <?php
            }
            ?>
            <script type="text/javascript">

                var hd_modal_loaded = false;

                function clear() {
                    var elements = document.querySelectorAll('.method-detail');
                    for (var i = 0; i < elements.length; i++) {
                        elements[i].innerText = "";
                        elements[i].style.height = "0";
                    }
                    Packeta.Widget.close();
                }

                jQuery('#ship-to-different-address-checkbox').change(function () {
                    resetAdressSelected()
                });

                jQuery(document).ready(function () {
                    jQuery('#billing_address_1, #billing_address_2, #billing_city, #billing_postcode, #billing_country, #billing_state').change(function () {
                        if (!jQuery('#ship-to-different-address-checkbox').is(':checked')) {
                            resetAdressSelected()
                        }
                    });
                });

                jQuery(document).ready(function () {
                    jQuery('#shipping_address_1, #shipping_address_2, #shipping_city, #shipping_postcode, #shipping_country, #shipping_state').change(function () {
                        if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {
                            resetAdressSelected()
                        }
                    });
                });

                function resetAdressSelected() {
                    var data = {
                        action: 'reset_vybrana_adresa',
                        reset_vybrana_adresa: true
                    };

                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                        //console.log(response);
                    });
                }

                function modalHDDialog(div) {

                    clear();

                    div = div[0]

                    var SelectedService = jQuery(document.body).find('input[name="shipping_method[0]"]:checked').val();

                    if (!SelectedService) {
                        SelectedService = jQuery(document.body).find('input[name="shipping_method[0]"]').val();
                    }

                    var data = {
                        action: 'toret_get_key',
                        nazev: SelectedService
                    };

                    let locationStreet;
                    let locationZip;
                    let locationCity;

                    if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {
                        locationStreet = jQuery('#shipping_address_1').val()
                        locationZip = jQuery('#shipping_postcode').val()
                        locationCity = jQuery('#shipping_city').val()
                    } else {
                        locationStreet = jQuery('#billing_address_1').val()
                        locationZip = jQuery('#billing_postcode').val()
                        locationCity = jQuery('#billing_city').val()
                    }

                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                        response = response.replace(/(\r\n|\n|\r)/gm, "");
                        response = response.trim();

                        if (response === 'paket')
                            response = 'packeta';

                        const packetaApiKey = '<?php echo $zasilkovna_option['api_key']; ?>';
                        const carrierId = response;

                        let options = {
                            layout: 'hd',
                            country: '<?php echo $packeta_country; ?>',
                            language: '<?php echo $packeta_language; ?>',
                            carrierId
                        }

                        if (locationCity !== '') {
                            options['centerCity'] = locationCity;
                            if (locationStreet !== '') {
                                options['centerStreet'] = locationStreet;
                            }
                        } else if (locationZip !== '') {
                            options['centerPostcode'] = locationZip;
                            if (locationStreet !== '') {
                                options['centerStreet'] = locationStreet;
                            }
                        }
                        Packeta.Widget.pick(packetaApiKey, showSelectedAddress.bind(div), options);
                    });
                }

                function showSelectedAddress(point) {

                    this.style.height = "auto";
                    this.style.width = "fit-content";
                    this.style.padding = "0 15px";

                    if (point && point.address) {
                        const {
                            country,
                            county,
                            city,
                            street,
                            houseNumber,
                            postcode
                        } = point.address;

                        if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {
                            jQuery('#shipping_address_1').val(street + ' ' + houseNumber)
                            jQuery('#shipping_address_2').val('')
                            jQuery('#shipping_postcode').val(postcode)
                            jQuery('#shipping_city').val(city)
                        } else {
                            jQuery('#billing_address_1').val(street + ' ' + houseNumber)
                            jQuery('#billing_address_2').val('')
                            jQuery('#billing_postcode').val(postcode)
                            jQuery('#billing_city').val(city)
                        }

                        let scrollingToElement = jQuery('#billing_address_1');
                        if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {
                            scrollingToElement = jQuery("#shipping_address_1");
                        }

                        jQuery([document.documentElement, document.body]).animate({
                            scrollTop: scrollingToElement.offset().top - 100
                        }, 200);

                        var data = {
                            action: 'vybrana_adresa',
                            vybrana_adresa: true
                        };

                        jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                            //console.log(response);
                        });
                    }
                }

                function open_modal_hd_click(e) {
                    e.preventDefault();
                    if (!hd_modal_loaded) {
                        modalHDDialog(document.getElementsByClassName('zasilkovna-hd-check-div'));
                        hd_modal_loaded = true;
                        setTimeout(function showpanel() {
                            hd_modal_loaded = false;
                        }, 1000)
                    }
                }

                icon_clickable = '<?php echo apply_filters('zasilkovna_pickup_icon_class', false);?>'

                if (icon_clickable) {
                    jQuery('body').on('click', '.zasikovna-hd-ico', function (e) {
                        e.preventDefault();
                        open_modal_click(e);
                    });
                }

                jQuery('body').off('click', '.zas-pop-hd-kont');
                jQuery('body').on('click', '.zas-pop-hd-kont', function (e) {
                    e.preventDefault();
                    if (typeof Packeta === 'undefined') {
                        alert("<?php _e('The branch selection service is currently unavailable. Please choose a different shipping option.', 'zasilkovna')?>");
                    } else {
                        if (!hd_modal_loaded) {
                            modalHDDialog(document.getElementsByClassName('zasilkovna-hd-check-div'));
                            hd_modal_loaded = true;
                            setTimeout(function showpanel() {
                                hd_modal_loaded = false;
                            }, 1000)
                        }
                    }
                });
            </script><?php
        }
    }

    public function woo_print_autoload_js()
    {
        if (tzas_better_is_checkout() && empty($wp->query_vars['order-pay']) && !isset($wp->query_vars['order-received'])) {

            $zasilkovna_option = get_option('zasilkovna_option');

            if (empty($zasilkovna_option['api_key'])) {
                return;
            }

            $country = toret_get_customer_country();

            $langs = explode('_', get_locale());

            $packeta_language = $langs[0];
            $packeta_country = strtolower($country);

            $show_on_select = '';
            if (isset($zasilkovna_option['tzas_modal_show_on_select'])) {
                $show_on_select = $zasilkovna_option['tzas_modal_show_on_select'];
            }

            $widget_position = "";
            $prefix = '';
            if (!empty($zasilkovna_option['widget_position']) && $zasilkovna_option['widget_position'] == 'after') {
                $widget_position = "after";
                $prefix = '<strong>' . __('Selected pickup point', 'zasilkovna') . '</strong>' . ': ';
            }

            $pickup_point_methods = array();

            $ToretZasilkovna = ToretZasilkovnaLib();
            $komplet_data = $ToretZasilkovna->Helper->get_komplet_data();

            $zasilkovna_option = get_option('zasilkovna_option', array());

            foreach ($komplet_data as $data) {
                if ($data['pobocky'] == 1) {
                    $pickup_point_methods[] = $data['prac'];
                }
            }

            $cod_check = $zasilkovna_option['cod_point_check'] ?? '';

            $disabledZBOXes = $ToretZasilkovna->Helper->is_disabled_zboxes();

            $pickup_point_methods[] = 'zasilkovna>z-points';
            $pickup_point_methods[] = 'zasilkovna>packeta-zpoints';
            $pickup_point_methods[] = 'zasilkovna>packeta-zbox';

            $load_in_enqueue = apply_filters('zasilkovna_load_in_enqueue', true);
            if (!$load_in_enqueue) {
                ?>
                <script src="<?php echo TORET_ZASILKOVNA_PICKUP_WIDGET_URL; ?>"></script>
                <?php
            }
            ?>

            <script type="text/javascript">

                var modal_loaded = false;
                var icon_clickable = true;

                jQuery(document).ready(function () {
                    jQuery(document.body).on('change', 'input[name="shipping_method[0]"]', function () {
                        jQuery('body').trigger('update_checkout');
                    });
                });

                function showSelectedPickupPoint(point) {

                    if (point == null) {
                        return;
                    }

                    var data = {
                        action: 'vybrana_pobocka',
                        point: point
                    };

                    let cod_check = '<?php echo $cod_check; ?>'

                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                        let group = (point ? point.group : "");
                        let newValue = 'true';
                        if (group === 'zbox') {
                            newValue = 'false';
                        }
                        document.getElementById('zasilkovna_creditCardPayment').value = newValue
                        sessionStorage.creditCardPayment = newValue;
                        if (cod_check === 'ok') {
                            jQuery('body').trigger('update_checkout');
                        }
                    });

                    this.style.height = "inherit";
                    let selectedText = point ? point.name : "";

                    if ("<?php echo $widget_position; ?>" === "after") {
                        selectedText = "<?php echo $prefix; ?>" + selectedText;
                    }

                    this.innerText = selectedText;
                    jQuery('.display_branch').html(this.innerText);
                    sessionStorage.zasilkovnaPobockaName = (point ? point.name : "");

                    document.getElementById('zasilkovna_id_zas').value = (point ? point.id : "default");
                    sessionStorage.zasilkovnaVybranaPobocka = (point ? point.id : "default");

                    document.getElementById('zasilkovna_carrierId').value = (point ? point.carrierId : "");
                    sessionStorage.zasilkovnacarrierId = (point ? point.carrierId : "");

                    document.getElementById('zasilkovna_carrierPickupPointId').value = (point ? point.carrierPickupPointId : "");
                    sessionStorage.zasilkovnacarrierPickupPointId = (point ? point.carrierPickupPointId : "");

                    document.getElementById('zasilkovna_name').value = (point ? point.name : "");

                    document.getElementById('zasilkovna_place').value = (point ? point.place : "");
                    sessionStorage.zasilkovnaVybranaPobockaPlace = (point ? point.place : "");

                    document.getElementById('zasilkovna_gps_lat').value = (point ? point.gps.lat : "");
                    sessionStorage.zasilkovnaVybranaPobockaGpsLat = (point ? point.gps.lat : "");

                    document.getElementById('zasilkovna_gps_lon').value = (point ? point.gps.lon : "");
                    sessionStorage.zasilkovnaVybranaPobockaGpsLon = (point ? point.gps.lon : "");

                    document.getElementById('zasilkovna_street').value = (point ? point.street : "");
                    sessionStorage.zasilkovnaVybranaPobockaStreet = (point ? point.street : "");

                    document.getElementById('zasilkovna_city').value = (point ? point.city : "");
                    sessionStorage.zasilkovnaVybranaPobockaCity = (point ? point.city : "");

                    document.getElementById('zasilkovna_country').value = (point ? point.country : "");
                    sessionStorage.zasilkovnaVybranaPobockaCountry = (point ? point.country : "");

                    document.getElementById('zasilkovna_zip').value = (point ? point.zip : "");
                    sessionStorage.zasilkovnaVybranaPobockaZip = (point ? point.zip : "");

                    document.getElementById('zasilkovna_url').value = (point ? point.url : "");
                    sessionStorage.zasilkovnaVybranaPobockaURL = (point ? point.url : "");

                    window.dispatchEvent(new CustomEvent("zasilkovnaPickupSelected", {detail: point}));

                    jQuery('#tzas-selected-row').show();
                    jQuery('body').trigger('updated_checkout');
                }

                function clear() {
                    var elements = document.querySelectorAll('.method-detail');
                    for (var i = 0; i < elements.length; i++) {
                        elements[i].innerText = "";
                        elements[i].style.height = "0";
                    }
                    try {
                        Packeta.Widget.close();
                    } catch (e) {

                    }
                }

                function modalDialog(div) {
                    clear();

                    var disabledZBOXes = "<? echo $disabledZBOXes;?>"
                    var SelectedService = jQuery(document.body).find('input[name="shipping_method[0]"]:checked').val();

                    if (!SelectedService) {
                        SelectedService = jQuery(document.body).find('input[name="shipping_method[0]"]').val();
                    }

                    var data = {
                        action: 'toret_get_key',
                        nazev: SelectedService
                    };

                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {

                        response = response.replace(/(\r\n|\n|\r)/gm, "");
                        response = response.trim();

                        if (response === 'paket')
                            response = 'packeta';

                        let carrier = response;
                        if (['packeta', 'packeta-zpoints', 'packeta-zbox'].includes(response)) {
                            carrier = 'packeta';
                        }

                        let packetaOptions = {
                            country: '<?php echo $packeta_country; ?>',
                            language: '<?php echo $packeta_language; ?>',
                            weight: 0,
                            appIdentity: '<?php echo WOOZASILKOVNAVER;?>',
                            carriers: carrier,
                        };

                        if ((response === 'packeta' && disabledZBOXes === 'yes') || response === 'packeta-zpoints') {
                            packetaOptions = {
                                weight: 0,
                                language: '<?php echo $packeta_language; ?>',
                                appIdentity: '<?php echo WOOZASILKOVNAVER;?>',
                                vendors: [
                                    {
                                        country: "<?php echo $packeta_country; ?>",
                                    }
                                ]
                            };
                        }

                        if (response === 'packeta-zbox') {
                            packetaOptions = {
                                weight: 0,
                                language: '<?php echo $packeta_language; ?>',
                                appIdentity: '<?php echo WOOZASILKOVNAVER;?>',
                                vendors: [
                                    {
                                        country: "<?php echo $packeta_country; ?>",
                                        group: "zbox"
                                    }
                                ]
                            };
                        }

                        Packeta.Widget.pick(
                            '<?php echo $zasilkovna_option['api_key']; ?>',
                            showSelectedPickupPoint.bind(div[0]),
                            packetaOptions
                        );
                    });
                }

                function open_modal_click(e) {
                    e.preventDefault();
                    if (typeof Packeta === 'undefined') {
                        alert("<?php _e('The branch selection service is currently unavailable. Please choose a different shipping option.', 'zasilkovna')?>");
                    } else {
                        if (!modal_loaded) {
                            modalDialog(document.getElementsByClassName('display_branch'));
                            modal_loaded = true;
                            setTimeout(function showpanel() {
                                modal_loaded = false;
                            }, 1000)
                        }
                    }
                }

                icon_clickable = '<?php echo apply_filters('zasilkovna_pickup_icon_class', false);?>'

                if (icon_clickable)
                    jQuery('body').on('click', '.zasikovna-ico', function (e) {
                        e.preventDefault();
                        open_modal_click(e);
                    });

                jQuery('body').off('click', '.zas-pop-kont');
                jQuery('body').on('click', '.zas-pop-kont', function (e) {
                    e.preventDefault();
                    if (typeof Packeta === 'undefined') {
                        alert("<?php _e('The branch selection service is currently unavailable. Please choose a different shipping option.', 'zasilkovna')?>");
                    } else {
                        if (!modal_loaded) {
                            modalDialog(document.getElementsByClassName('display_branch'));
                            modal_loaded = true;
                            setTimeout(function showpanel() {
                                modal_loaded = false;
                            }, 1000)
                        }
                    }
                });

                var jqueryarray = <?php echo json_encode($pickup_point_methods); ?>;

                jQuery(document.body).on('change', 'input[name="shipping_method[0]"]', function () {

                    if (jQuery(".woocommerce-checkout-review-order").filter(":hidden").length === 0) {

                        let value = this.value;

                        if (jQuery.inArray(value, jqueryarray) !== -1) {

                            if ("<?php echo $show_on_select; ?>" === 'ok') {

                                setTimeout(function () {

                                    if (jQuery('.display_branch').length) {
                                        if (!modal_loaded) {
                                            if (typeof Packeta === 'undefined') {
                                                alert("<?php _e('The branch selection service is currently unavailable. Please choose a different shipping option.', 'zasilkovna')?>");
                                            } else {
                                                modal_loaded = true;
                                                modalDialog(document.getElementsByClassName('display_branch'));
                                            }
                                        }
                                    } else {
                                        setTimeout(function () {

                                            if (jQuery('.display_branch').length) {
                                                if (!modal_loaded) {
                                                    if (typeof Packeta === 'undefined') {
                                                        alert("<?php _e('The branch selection service is currently unavailable. Please choose a different shipping option.', 'zasilkovna')?>");
                                                    } else {
                                                        modal_loaded = true;
                                                        modalDialog(document.getElementsByClassName('display_branch'));
                                                    }
                                                }
                                            } else {
                                                setTimeout(function () {

                                                    if (jQuery('.display_branch').length) {
                                                        if (!modal_loaded) {
                                                            if (typeof Packeta === 'undefined') {
                                                                alert("<?php _e('The branch selection service is currently unavailable. Please choose a different shipping option.', 'zasilkovna')?>");
                                                            } else {
                                                                modal_loaded = true;
                                                                modalDialog(document.getElementsByClassName('display_branch'));
                                                            }
                                                        }
                                                    }

                                                }, 1000);
                                            }

                                        }, 2000);
                                    }

                                }, 2000);

                            }
                        }
                    }

                });


                jQuery(document.body).on('change', 'input[name="payment_method"]', function () {
                    jQuery('body').trigger('update_checkout');
                });
                jQuery(document.body).on('change', 'input[name="shipping_method[0]"]', function () {
                    jQuery('body').trigger('update_checkout');
                });

            </script><?php

        } else {
            return;
        }
    }

    /**
     * Add info to order detail
     */
    public function zasilkovna_admin_customer_order_info(object $order): void
    {

        $ToretZasilkovna = ToretZasilkovnaLib();
        $order_id = $order->get_id();

        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');
        $zasilkovna_service = tzas_get_service_from_string($zasilkovna_shipping);

        if (tzas_is_zasilkovna_shipping($zasilkovna_shipping)) {
            if (tzas_is_native_method($zasilkovna_service)) {
                $html = $ToretZasilkovna->Outputs->admin_customer_order_info($order_id, $zasilkovna_shipping);
                echo $html;
            } else {
                $ZasilkovnaCarrier = $ToretZasilkovna->Helper->GetServiceBySlug($zasilkovna_service);
                if (($ZasilkovnaCarrier['pobocky'] ?? 0) == 1) {
                    $html = $ToretZasilkovna->Outputs->admin_customer_order_info($order_id, $zasilkovna_shipping);
                    echo $html;
                }
            }
        }

        $barcodes_string = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode');
        $barcodes = explode(';', $barcodes_string);

        if (!empty($barcodes_string)) {

            $i = 1;
            foreach ($barcodes as $barcode) {
                $links[] = $ToretZasilkovna->Outputs->sledovani_link_gathered($order_id, $barcode);
                $i++;
            }

            if (!empty($links)) {
                $links = implode(', ', $links);
                $link = __('Tracking link', 'zasilkovna') . ' ' . $order->get_shipping_method() . ' :' . $links;
            }

            echo __('Order Status in Packeta:', 'zasilkovna') . '<br /><b>' . $ToretZasilkovna->Outputs->packetStatus($order_id) . '</b>';

        } else {

            if ($zasilkovna_service) {
                $service_data = $ToretZasilkovna->Helper->GetServiceBySlug($zasilkovna_service);
            }

            if (tzas_is_native_method($zasilkovna_service) || (isset($service_data['pobocky']) && $service_data['pobocky'] == 1)) {

                $zasilkovna_option = get_option('zasilkovna_option');
                if (!empty($zasilkovna_option['api_key'])) {

                    $order = wc_get_order($order_id);
                    $country = $order->get_shipping_country();

                    if ($country == 'CZ') {
                        $packeta_country = 'cz';
                    } else {
                        $packeta_country = strtolower($country);
                    }

                    $langs = explode('_', get_locale());
                    $packeta_language = $langs[0];

                    $disabledZBOXes = $ToretZasilkovna->Helper->is_disabled_zboxes_order($order);

                    $load_in_enqueue = apply_filters('zasilkovna_load_in_enqueue', true);
                    if (!$load_in_enqueue) {
                        ?>
                        <script src="<?php echo TORET_ZASILKOVNA_PICKUP_WIDGET_URL; ?>"></script>
                        <?php
                    }
                    ?>

                    <script type="text/javascript">

                        function showSelectedPickupPoint(point) {

                            var data = {
                                action: 'zmenit_pobocku',
                                id: '<?php echo $order_id; ?>',
                                point: point
                            };

                            jQuery.post(ajaxurl, data, function (response) {
                                location.reload();
                            });

                        }

                        function clear() {
                            var elements = document.querySelectorAll('.method-detail');
                            for (var i = 0; i < elements.length; i++) {
                                elements[i].innerText = "";
                                elements[i].style.height = "0";
                            }
                            Packeta.Widget.close();
                        }

                        function modalDialog(div) {
                            clear();

                            let disabledZBOXes = "<? echo $disabledZBOXes;?>"

                            let native_type = jQuery(".zas-pop-kont").data('native')

                            let data = {
                                action: 'toret_get_key',
                                nazev: '<?php echo Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy') ?>'
                            };

                            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                                response = response.replace(/(\r\n|\n|\r)/gm, "");
                                response = response.trim();

                                if (response === 'paket')
                                    response = 'packeta';

                                let carrier = response;
                                if (['packeta', 'packeta-zpoints', 'packeta-zbox'].includes(response)) {
                                    carrier = 'packeta';
                                }

                                let packetaOptions = {
                                    country: '<?php echo $packeta_country; ?>',
                                    language: '<?php echo $packeta_language; ?>',
                                    weight: 0,
                                    appIdentity: '<?php echo WOOZASILKOVNAVER;?>',
                                    carriers: carrier,
                                };

                                if ((response === 'packeta' && disabledZBOXes === 'yes') || response === 'packeta-zpoints') {
                                    packetaOptions = {
                                        weight: 0,
                                        language: '<?php echo $packeta_language; ?>',
                                        appIdentity: '<?php echo WOOZASILKOVNAVER;?>',
                                        vendors: [
                                            {
                                                country: "<?php echo $packeta_country; ?>",
                                                //group: "zbox"
                                            }
                                        ]
                                    };
                                }

                                if (response === 'packeta-zbox') {
                                    packetaOptions = {
                                        weight: 0,
                                        language: '<?php echo $packeta_language; ?>',
                                        appIdentity: '<?php echo WOOZASILKOVNAVER;?>',
                                        vendors: [
                                            {
                                                country: "<?php echo $packeta_country; ?>",
                                                group: "zbox"
                                            }
                                        ]
                                    };
                                }


                                Packeta.Widget.pick(
                                    '<?php echo $zasilkovna_option['api_key']; ?>',
                                    showSelectedPickupPoint.bind(div[0]),
                                    packetaOptions
                                );
                            });
                        }

                        jQuery('body').on('click', '.zas-pop-kont', function (e) {
                            e.preventDefault();
                            if (typeof Packeta === 'undefined') {
                                alert("<?php _e('The branch selection service is currently unavailable. Please choose a different shipping option.', 'zasilkovna')?>");
                            } else {
                                modalDialog(document.getElementsByClassName('display_branch'));
                            }
                        });

                    </script>

                    <a class="zas-pop-kont button torlib-secondary-button"
                       data-native="<?php echo tzas_get_native_slug_from_service($zasilkovna_service); ?>"><?php _e('Change pick-up point', 'zasilkovna'); ?></a>
                    <?php
                }
            }
        }
    }

    /**
     * Add info to order detail
     */
    public function zasilkovna_customer_order_info(object $order): void
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $order_id = $order->get_id();

        $zasilkovna_id = (int)Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_pobocky');

        $barcodes_string = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode');
        $barcodes = explode(';', $barcodes_string);

        if (!empty($zasilkovna_id)) {

            $zas = $ToretZasilkovna->Helper->set_services();

            if (!in_array($zasilkovna_id, $zas)) {

                $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');

                $html = '<table class="shop_table order_details zasilkovna_detail">';
                $html .= $ToretZasilkovna->Outputs->customer_order_info_table($order_id, $zasilkovna_shipping);
                if (!empty($barcodes_string)) {
                    $html .= '<tr>';
                    $html .= '<th>' . __('Track the shipment online: ', 'zasilkovna') . '</th>';
                    $html .= '<td>';

                    $i = 1;
                    foreach ($barcodes as $barcode) {
                        $links[] = $ToretZasilkovna->Outputs->sledovani_link_gathered($order_id, $barcode);
                        $i++;
                    }
                    if (!empty($links)) {
                        $links = implode(', ', $links);
                        $html .= __('Tracking link', 'zasilkovna') . ' ' . $order->get_shipping_method() . ' :' . $links;
                    }
                    $html .= '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';

            } else {

                if (!empty($barcodes_string)) {
                    $html = '<table class="shop_table order_details zasilkovna_detail">';
                    $html .= '<tr>';
                    $html .= '<th>' . __('Track the shipment online: ', 'zasilkovna') . '</th>';
                    $html .= '<td>';
                    $i = 1;
                    foreach ($barcodes as $barcode) {
                        $links[] = $ToretZasilkovna->Outputs->sledovani_link_gathered($order_id, $barcode);
                        $i++;
                    }
                    if (!empty($links)) {
                        $links = implode(', ', $links);
                        $html .= __('Tracking link', 'zasilkovna') . ' ' . $order->get_shipping_method() . ' :' . $links;
                    }
                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</table>';
                }
            }

            if (!empty($html))
                echo apply_filters('tzas_customer_details_order_info', $html, $order_id);
        }
    }

    /**
     * Add info to email
     */
    public
    function zasilkovna_customer_email_info(object $order): void
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $order_id = $order->get_id();

        $zasilkovna_id = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_pobocky');

        if (!empty($zasilkovna_id)) {
            $zas = $ToretZasilkovna->Helper->set_services();
            if (!in_array($zasilkovna_id, $zas)) {
                $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');
                $html = $ToretZasilkovna->Outputs->customer_email_info($order_id, $zasilkovna_shipping);
                echo $html;
            }
        }
    }

    /**
     * Add info to email
     */
    public function zasilkovna_customer_email_tracking_info(object $order): void
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $order_id = $order->get_id();

        $zasilkovna_id = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_pobocky');
        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');

        $barcodes_string = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode');
        $barcodes = explode(';', $barcodes_string);

        $link = '';
        if (!empty($barcodes_string)) {
            $links = [];
            $i = 1;
            foreach ($barcodes as $barcode) {
                $links[] = $ToretZasilkovna->Outputs->sledovani_link_gathered($order_id, $barcode);
                $i++;
            }

            if (!empty($links)) {
                $links = implode(', ', $links);
                $link = '<p>' . __('Tracking link', 'zasilkovna') . ' ' . $order->get_shipping_method() . ' :' . $links . '</p>';
            }

            echo apply_filters('zasilkovna_email_tracking_link', $link, $order_id, $barcodes);
        }
    }

    /**
     * Create select option for Zasilkovna branches
     */
    public function zasilkovna_select_option()
    {
        ob_start();

        $shipping_method = tzas_get_shipping_from_cart();
        $shipping_service = tzas_get_service_from_cart();

        if (!empty($shipping_service)) {

            $country = toret_get_customer_country();

            $ToretZasilkovna = ToretZasilkovnaLib();
            $komplet_data = $ToretZasilkovna->Helper->get_komplet_data();

            $klic = $pobocky = 0;
            $slug = '';
            foreach ($komplet_data as $key => $data) {
                if ($data['prac'] == $shipping_method && $data['pobocky'] == 1) {
                    $pobocky = $data['pobocky'];
                    $slug = $data['slug'];
                } elseif ($data['prac'] == $shipping_method && $data['pobocky'] == 0) {
                    $klic = $key;
                }
            }

            $zasilkovna_option = get_option('zasilkovna_option');

            if (tzas_is_native_pickup_method($shipping_service)) {
                $ico_url = $this->get_zasilkovna_icon_select($zasilkovna_option, 'icon_select_url_' . strtolower($country));
            } else if ($pobocky == 1) {
                $ico_url = $this->get_zasilkovna_icon_select($zasilkovna_option, 'icon_select_url_' . $slug);
            }

            if (empty($ico_url)) {
                if ($country == 'CZ')
                    $ico_url = WOOZASILKOVNAURL . 'assets/images/select_cz.svg';
                else
                    $ico_url = WOOZASILKOVNAURL . 'assets/images/select_eu.svg';
            }

            if ($pobocky == 1 || tzas_is_native_pickup_method($shipping_service)) {


                if (apply_filters('zasilkovna_render_select_branch_markup', true)) {

                    if (empty($zasilkovna_option['widget_position']) || $zasilkovna_option['widget_position'] != 'after') {

                        echo '<tr>';
                        if (get_option('tzas_show_pickup_icon', 'ok') == 'ok') {
                            echo '<th class="zasikovna-ico" style="cursor:pointer;"><img src="' . $ico_url . '" alt="Packeta" style="' . get_option('tzas_icon_pickup_custom_css') . '" /></th>';
                        } else {
                            echo '<th class="zasikovna-ico" style="cursor:pointer;"></th>';
                        }
                        echo '<td>';
                        echo '<a class="button zas-pop-kont zas-tlac" data-native="' . tzas_get_native_slug_from_service($shipping_service) . '" style="cursor: pointer;">' . __('Select a pick-up point', 'zasilkovna') . '</a><a style="display:none;cursor: pointer" href="#" class="zas-tlac2 button packeta-selector-open">' . __('Select a pick-up point', 'zasilkovna') . '</a>';
                        echo '</td>';
                        echo '</tr>';

                        if (empty(WC()->session->get('PacketaPointData')))
                            echo '<tr style="display: none;" id="tzas-selected-row">';
                        else
                            echo '<tr id="tzas-selected-row">';

                        $selected_label = apply_filters('zasilkovna_selected_point_label_filter', __('Selected store', 'zasilkovna'));

                        echo '<th class="zasilkovna-zvolena-pobocka">' . $selected_label . '</th>';
                        echo '<td>';
                        echo '<div class="packeta-selector-branch-name method-detail toret-vyber-pobocky display_branch" data-native="' . tzas_get_native_slug_from_service($shipping_service) . '"></div>';
                    }

                    echo '<input type="hidden" name="zasilkovna_id" id="zasilkovna_id_zas" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_carrierId" id="zasilkovna_carrierId" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_carrierPickupPointId" id="zasilkovna_carrierPickupPointId" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_name" id="zasilkovna_name" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_place" id="zasilkovna_place" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_gps_lat" id="zasilkovna_gps_lat" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_gps_lon" id="zasilkovna_gps_lon" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_street" id="zasilkovna_street" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_city" id="zasilkovna_city" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_country" id="zasilkovna_country" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_zip" id="zasilkovna_zip" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_url" id="zasilkovna_url" class="zasilkovna_id" value="default" />';
                    echo '<input type="hidden" name="zasilkovna_creditCardPayment" id="zasilkovna_creditCardPayment" class="zasilkovna_id" value="" />';

                    if (empty($zasilkovna_option['widget_position']) || $zasilkovna_option['widget_position'] != 'after') {
                        echo '</td>';
                        echo '</tr>';
                    }
                }
            } else {

                if (in_array($country, TORET_ZASILKOVNA_HD_WIDGET_COUNTRIES) && !empty($zasilkovna_option['api_key'])) {
                    if ($zasilkovna_option['enableHDChecker'] ?? '' == 'ok') {

                        if (empty($zasilkovna_option['widget_position']) || $zasilkovna_option['widget_position'] != 'after') {

                            $ico_url = apply_filters('tzas_hd_icon_url', $ico_url);
                            echo '<tr>';
                            if (get_option('tzas_show_pickup_icon', 'ok') == 'ok') {
                                echo '<th class="zasikovna-ico-hd" style="cursor:pointer;"><img src="' . $ico_url . '" alt="Packeta" style="' . get_option('tzas_icon_pickup_custom_css') . '" /></th>';
                            } else {
                                echo '<th class="zasikovna-ico-hd" style="cursor:pointer;"></th>';
                            }
                            echo '<td>';

                            echo '<a class="button zas-pop-hd-kont zas-hd-tlac" style="cursor: pointer;">' . __('Select address', 'zasilkovna') . '</a><a style="display:none;cursor: pointer" href="#" class="zas-hd-tlac2 button packeta-selector-open">' . __('Select address', 'zasilkovna') . '</a>';
                            echo '</td>';
                            echo '</tr>';
                            echo '<tr style="display: none;" id="tzas-selected-hd-row">';
                        }

                        $selected_label = '';

                        echo '<th class="zasilkovna-hd-check-label">' . $selected_label . '</th>';
                        echo '<td>';
                        echo '<div class="zasilkovna-hd-check-div method-detail"></div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                }

                WC()->session->__unset('PacketaShippingData');
                WC()->session->__unset('PacketaPointData');
                WC()->session->__unset('zasilkovna_creditCardPayment');

                WC()->session->__unset('addressSelected');
                WC()->session->set('zasilkovna_creditCardPayment', "true");
                WC()->session->set('PacketaPointData', array());
                WC()->session->set('PacketaShippingData', $klic);

                echo '<input type="hidden" name="zasilkovna_id" id="zasilkovna_id_zas_service" class="zasilkovna_id" value="' . $klic . '" />';
            }
        }

        $output = ob_get_clean();
        if (has_filter('zasilkovna_select_option_output')) {
            echo apply_filters('zasilkovna_select_option_output', $output, $this);
        } else {
            echo $output;
        }

    }

    /**
     * Získat ikonu Packetay
     */
    function get_zasilkovna_icon(array $zasilkovna_option, string $icon): string
    {
        if (!empty($zasilkovna_option[$icon])) {
            $ico_url = $zasilkovna_option[$icon];
        } else {
            $ico_url = WOOZASILKOVNAURL . 'assets/images/zasilkovna.svg';
        }

        return $ico_url;
    }

    /**
     * Získat ikonu Packetay
     */
    function get_zasilkovna_icon_select(array $zasilkovna_option, string $icon): string
    {
        if (!empty($zasilkovna_option[$icon])) {
            $ico_url = $zasilkovna_option[$icon];
        } else {
            $ico_url = '';
        }

        return $ico_url;
    }

    /**
     * Save id místa
     */
    function store_pickup_field_update_order_meta(int $order_id): void
    {
        $shipping_method = tzas_get_shipping_from_cart();
        $zasilkovna_option = get_option('zasilkovna_option');

        $saveToLog = (isset($zasilkovna_option['branchLog']) && ($zasilkovna_option['branchLog'] == 'ok'));

        $logData = [
                'inside' => 'store_pickup_field_update_order_meta: ' . current_action(),
                'shippingMethod' => $shipping_method,
        ];

        $log = [
                'orderid' => $order_id,
                'context' => 'Pickup point selection',
                'type' => 4,
        ];


        if (tzas_is_zasilkovna_shipping($shipping_method)) {

            $PacketaPointData = WC()->session->get('PacketaPointData');
            $PacketaShippingData = WC()->session->get('PacketaShippingData');

            $order = wc_get_order($order_id);

            $use_carrier_id = false;

            $source_data = null;
            if (isset($PacketaPointData['zasilkovna_id']) && $PacketaPointData['zasilkovna_id'] != 'default') {
                $source_data = $PacketaPointData;
                $source = 'session';
            } elseif (isset($_POST['zasilkovna_id'])) {
                $source_data = $_POST;
                $source = '$_POST';
            } else {
                $source = 'unknown';
                $use_carrier_id = true;
            }

            $logData['sourceData'] = $source_data;
            $logData['source'] = $source;
            $logData['userCarrierId'] = $use_carrier_id;
            $logData['changeShippingAddress'] = $zasilkovna_option['change_shipping_address'] ?? 'unknown';

            if (!empty($source_data)) {

                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_pobocky', esc_attr($source_data['zasilkovna_id']));

                if (isset($source_data['zasilkovna_carrierId'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_carrierId', esc_attr($source_data['zasilkovna_carrierId']));
                }

                if (isset($source_data['zasilkovna_carrierPickupPointId'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_carrierPickupPointId', esc_attr($source_data['zasilkovna_carrierPickupPointId']));
                }

                if (isset($source_data['zasilkovna_name'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_name', esc_attr($source_data['zasilkovna_name']));
                }

                if (isset($source_data['zasilkovna_place'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_place', esc_attr($source_data['zasilkovna_place']));
                    if (!empty($zasilkovna_option['change_shipping_address']) && $zasilkovna_option['change_shipping_address'] == 'ok') {
                        $order->set_shipping_company(esc_attr($source_data['zasilkovna_place']));
                    }
                }

                if (isset($source_data['zasilkovna_gps_lat'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_gps_lat', esc_attr($source_data['zasilkovna_gps_lat']));
                }

                if (isset($source_data['zasilkovna_gps_lon'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_gps_lon', esc_attr($source_data['zasilkovna_gps_lon']));
                }

                if (isset($source_data['zasilkovna_street'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_street', esc_attr($source_data['zasilkovna_street']));
                    if (!empty($zasilkovna_option['change_shipping_address']) && $zasilkovna_option['change_shipping_address'] == 'ok') {
                        $order->set_shipping_address_1(esc_attr($source_data['zasilkovna_street']));
                    }
                }

                if (isset($source_data['zasilkovna_city'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_city', esc_attr($source_data['zasilkovna_city']));
                    if (!empty($zasilkovna_option['change_shipping_address']) && $zasilkovna_option['change_shipping_address'] == 'ok') {
                        $order->set_shipping_city(esc_attr($source_data['zasilkovna_city']));
                    }
                }

                if (isset($source_data['zasilkovna_country'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_country', esc_attr($source_data['zasilkovna_country']));
                }

                if (isset($source_data['zasilkovna_zip'])) {
                    Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_zip', esc_attr($source_data['zasilkovna_zip']));
                    if (!empty($zasilkovna_option['change_shipping_address']) && $zasilkovna_option['change_shipping_address'] == 'ok') {
                        $order->set_shipping_postcode(esc_attr($source_data['zasilkovna_zip']));
                    }
                }
            }

            Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_dopravy', $shipping_method);

            if ($PacketaShippingData != '' && $use_carrier_id) {
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_pobocky', $PacketaShippingData);
            }


            Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_creditCardPayment', WC()->session->get('zasilkovna_creditCardPayment'));

            $order->save();

            WC()->session->set('PacketaPointData', array());
            WC()->session->set('PacketaShippingData', '');
            WC()->session->set('zasilkovna_creditCardPayment', 'true');
        }

        $log['log'] = json_encode($logData);
        if ($saveToLog) {
            zasilkovna_log($log);
        }
    }

    /**
     * Zkontrolovat vybrání pobočky
     */
    public function zasilkovna_check_pobocka()
    {
        if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {

            $doprava_full_name = tzas_get_shipping_from_cart();
            $zasilkovna_service = tzas_get_service_from_cart();

            $ToretZasilkovna = ToretZasilkovnaLib();
            $komplet_data = $ToretZasilkovna->Helper->komplet_data();

            $pobocky_enabled = false;

            foreach ($komplet_data as $data) {
                if ($data['prac'] == $doprava_full_name && isset($data['pobocky']) && $data['pobocky'] == 1) {
                    $pobocky_enabled = true;
                    break;
                }
            }

            $error_shown = false;
            $pickup_point_required = !empty($zasilkovna_service) && (tzas_is_native_pickup_method($zasilkovna_service) || $pobocky_enabled);

            if ($pickup_point_required) {
                $PacketaPointData = WC()->session->get('PacketaPointData');
                $zasilkovna_id = null;

                if (isset($PacketaPointData['zasilkovna_id'])) {
                    $zasilkovna_id = $PacketaPointData['zasilkovna_id'];
                } elseif (isset($_POST['zasilkovna_id'])) {
                    $zasilkovna_id = $_POST['zasilkovna_id'];
                }

                if (empty($zasilkovna_id) || $zasilkovna_id === 'default') {
                    $text = __('Selected shipping method needs pick-up point to be selected.', 'zasilkovna');
                    $text = apply_filters('zasilkovna_pickup_check_error_text', $text);
                    wc_add_notice($text, 'error');
                    $error_shown = true;
                }

                if (!$error_shown) {
                    $country = WC()->customer->get_shipping_country();
                    $selected_country = null;

                    if (isset($PacketaPointData['zasilkovna_country'])) {
                        $selected_country = $PacketaPointData['zasilkovna_country'];
                    } elseif (isset($_POST['zasilkovna_country'])) {
                        $selected_country = $_POST['zasilkovna_country'];
                    }

                    if ($selected_country && strtolower($selected_country) !== strtolower($country)) {
                        $text = __('The selected pick-up point is not valid for your shipping country.', 'zasilkovna');
                        $text = apply_filters('zasilkovna_pickup_country_check_error_text', $text);
                        wc_add_notice($text, 'error');
                        $error_shown = true;
                    }
                }
            }

            if (tzas_is_zasilkovna_shipping($doprava_full_name) && !$error_shown && !tzas_is_native_method($zasilkovna_service) && !$pobocky_enabled) {
                $zasilkovna_option = get_option('zasilkovna_option');
                $selectedAddress = WC()->session->get('addressSelected');
                $country = WC()->customer->get_shipping_country();

                if (isset($zasilkovna_option['forceHDChecker']) && $zasilkovna_option['forceHDChecker'] == 'ok' && $country == 'CZ') {
                    if (!$selectedAddress) {
                        $text = __('Please select an address from Packeta widget.', 'zasilkovna');
                        $text = apply_filters('zasilkovna_hd_check_error_text', $text);
                        wc_add_notice($text, 'error');
                    }
                }
            }
        }
    }

    /**
     * Force WooCommerce to load email template from plugin
     */
    public
    function toret_locate_template($template, $template_name): string
    {
        if ($template_name == 'zasilkovna-admin-error-info.php') {
            $template = WOOZASILKOVNADIR . 'includes/emails/zasilkovna-admin-error-info.php';
        } elseif ($template_name == 'zasilkovna-admin-error-info-plain.php') {
            $template = WOOZASILKOVNADIR . 'includes/emails/zasilkovna-admin-error-info-plain.php';
        }
        return $template;
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function create_dopravci_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table = "CREATE TABLE {$wpdb->prefix}zasilkovna_dopravci (
        ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        dopravce_id bigint(20) NOT NULL,
        nazev varchar(100) NOT NULL,
        stat varchar(20) NOT NULL,
        statnazev varchar(100) NOT NULL,
        pobocky int(11) NOT NULL DEFAULT 0,
        api int(11) NOT NULL DEFAULT 0,
        dobirka int(11) NOT NULL DEFAULT 0,
        deklarace int(11) NOT NULL DEFAULT 0,
        rozmery int(11) NOT NULL DEFAULT 0,
        vaha int(11) NOT NULL DEFAULT 0,
        slug varchar(100) NOT NULL,
        prac varchar(100) NOT NULL,
        active int(11) NOT NULL DEFAULT 1,
        removed int(11) NOT NULL DEFAULT 0,
        type varchar(100) NOT NULL,
        PRIMARY KEY  (ID)
    ) $charset_collate;";

        dbDelta($table);
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function create_country_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table = "CREATE TABLE {$wpdb->prefix}zasilkovna_staty (
        ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        stat varchar(20) NOT NULL,
        statnazev varchar(100) NOT NULL,
        PRIMARY KEY  (ID)
    ) $charset_collate;";

        dbDelta($table);
    }

    public function zasilkovna_add_cart_weight($order_id)
    {
        global $woocommerce;

        $weight = $woocommerce->cart->cart_contents_weight;
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_cart_weight', $weight);
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_cart_weight_units', get_option('woocommerce_weight_unit'));
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_cart_weight_kg', (new ToretZasilkovnaDimensionHelper())->get_cart_total_weight());
    }

    function filter_woocommerce_cart_shipping_method_full_label($label, $method)
    {
        if (tzas_is_zasilkovna_shipping($method->method_id)) {

            $country = toret_get_customer_country();

            $ToretZasilkovna = ToretZasilkovnaLib();
            $komplet_data = $ToretZasilkovna->Helper->get_komplet_data();

            $zasilkovna_option = get_option('zasilkovna_option');

            $staty = $ToretZasilkovna->Helper->zasilkovna_kde();

            foreach ($komplet_data as $key => $data) {
                if ($data['prac'] == $method->id && $data['pobocky'] == 1) {
                    $ico_url = $this->get_zasilkovna_icon($zasilkovna_option, 'icon_url_' . $data['slug']);
                    break;
                } elseif ($data['prac'] == $method->id && $data['pobocky'] == 0) {
                    $ico_url = $this->get_zasilkovna_icon($zasilkovna_option, 'icon_url_' . $data['slug']);
                    break;
                }
            }

            $zasilkovna_service = tzas_get_service_from_string($method->id);
            if (in_array(strtolower($country), $staty) && tzas_is_native_method($zasilkovna_service)) {
                $ico_url = $this->get_zasilkovna_icon($zasilkovna_option, 'icon_url_' . strtolower($country));
            }

            if (empty($ico_url)) {
                $ico_url = WOOZASILKOVNAURL . 'assets/images/zasilkovna.svg';
            }

            return '<img alt="Packetka" src="' . $ico_url . '" class="tzas-label-img" style="' . get_option('tzas_icon_custom_css') . '"/>' . $label;
        } else {
            return $label;
        }
    }

    /**
     * Automatic send
     */
    public function send_ticket_automatic(int $order_id): void
    {
        $log_data = array(
                'order_id' => $order_id,
                'timestamp' => current_time('mysql'),
                'steps' => array()
        );

        $zasilkovna_send = get_option('zasilkovna_send');
        $log_data['zasilkovna_send'] = $zasilkovna_send;

        if (!empty($zasilkovna_send)) {
            $log_data['steps'][] = 'zasilkovna_send is NOT empty';

            $order = new WC_Order($order_id);
            $new_status = $order->get_status();
            $orderPaymentStatus = $order->get_payment_method();

            $log_data['order_status'] = $new_status;
            $log_data['payment_method'] = $orderPaymentStatus;

            $ToretZasilkovna = ToretZasilkovnaLib();
            $vaha = ToretZasilkovnaDimensionHelper::get_zasilkovna_weight($order_id);
            $log_data['vaha'] = $vaha;

            $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');
            $zasilkovna_service = tzas_get_service_from_string($zasilkovna_shipping);

            $log_data['zasilkovna_shipping'] = $zasilkovna_shipping;
            $log_data['zasilkovna_service'] = $zasilkovna_service;

            $rozmery = 0;
            $shippingID = '';
            $rozmery_data = '';
            $deklarace = 'ano';
            $apiAlowed = '1';

            if (tzas_is_zasilkovna_shipping($zasilkovna_shipping)) {
                $log_data['steps'][] = 'IS Zasilkovna shipping';

                $komplet_data = $ToretZasilkovna->Helper->komplet_data();
                //$log_data['komplet_data'] = $komplet_data;

                if (tzas_is_native_method($zasilkovna_service)) {
                    $log_data['steps'][] = 'Is NATIVE method';
                    $shippingID = (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierId') != 'undefined' ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierId') : '');
                    if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierId') == 'null') {
                        $shippingID = '';
                    }
                    $log_data['shippingID_native'] = $shippingID;
                } else {
                    $log_data['steps'][] = 'Is NOT native method';
                }

                if ($shippingID == '') {
                    $log_data['steps'][] = 'shippingID is EMPTY - searching in komplet_data';
                    foreach ($komplet_data as $data) {
                        if ($data['prac'] == $zasilkovna_shipping) {
                            $rozmery = $data['rozmery'];
                            //$log_data['steps'][] = 'Found matching prac - rozmery: ' . $rozmery;
                        }
                        if ($data['deklarace'] != 1) {
                            $deklarace = 'ne';
                            //$log_data['steps'][] = 'deklarace set to: ne';
                        }
                        if (isset($data['api'])) {
                            $apiAlowed = $data['api'];
                            //$log_data['steps'][] = 'apiAlowed set to: ' . $apiAlowed;
                        }
                    }
                } else {
                    $log_data['steps'][] = 'shippingID is NOT empty - getting service by ID';
                    $service = $ToretZasilkovna->Helper->GetServiceByID($shippingID);
                    $log_data['service_data'] = $service;
                    $rozmery = $service['rozmery'];
                    if ($service['deklarace'] != 1) {
                        $deklarace = 'ne';
                    }
                    if (isset($service['api'])) {
                        $apiAlowed = $service['api'];
                    }
                }

                $log_data['final_rozmery'] = $rozmery;
                $log_data['final_deklarace'] = $deklarace;
                $log_data['final_apiAlowed'] = $apiAlowed;

                if ($rozmery > 0) {
                    $rozmery_data = (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension') ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension') : '');
                    $log_data['rozmery_data'] = $rozmery_data;
                }

                if (!$apiAlowed) {
                    $log_data['result'] = 'FAILED';
                    $log_data['reason'] = 'API NOT ALLOWED';

                    zasilkovna_log(array(
                            'order_id' => $order_id,
                            'context' => __('Auto ticket send failed - API not allowed', TORETZASILKOVNASLUG),
                            'log' => json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    ));
                    return;
                }

                $package_count = 1;
                $zasilkovna_option = get_option('zasilkovna_option', array());
                //$log_data['zasilkovna_option'] = $zasilkovna_option;

                $order = wc_get_order($order_id);
                $max_dim = Dimensions::get_order_max_dimension($order, false, true);
                $max_dim_sum = Dimensions::get_order_max_sides_sum($order, false, true);
                $log_data['max_dim'] = $max_dim;
                $log_data['max_dim_sum'] = $max_dim_sum;

                $multipackage_data = $ToretZasilkovna->Helper->get_multipackage_data($zasilkovna_option, $vaha, $max_dim, $max_dim_sum);
                $log_data['multipackage_data'] = $multipackage_data;

                if ($multipackage_data['enabled']) {
                    $package_count = $multipackage_data['qty'];
                    $log_data['steps'][] = 'Multipackage ENABLED - package_count: ' . $package_count;
                }
                $log_data['package_count'] = $package_count;

                if ($deklarace == 'ne') {
                    $log_data['steps'][] = 'Deklarace is NE - checking conditions';

                    if ($rozmery == 0) {
                        $log_data['steps'][] = 'Rozmery is 0';

                        if ($vaha > 0) {
                            $log_data['steps'][] = 'Vaha > 0 - checking status mapping';

                            if (isset($zasilkovna_send['status'][$orderPaymentStatus])) {
                                $expected_status = $zasilkovna_send['status'][$orderPaymentStatus];
                                $log_data['expected_status'] = $expected_status;

                                if ($expected_status == $new_status) {
                                    $log_data['steps'][] = 'STATUS MATCH! Sending ticket...';
                                    $log_data['result'] = 'SUCCESS';

                                    $ToretZasilkovna = ToretZasilkovnaLib();
                                    $ToretZasilkovna->Send->send_ticket($order_id, $package_count);

                                    zasilkovna_log(array(
                                            'order_id' => $order_id,
                                            'context' => __('Auto ticket sent successfully', TORETZASILKOVNASLUG),
                                            'log' => json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                    ));
                                    return;
                                } else {
                                    $log_data['result'] = 'FAILED';
                                    $log_data['reason'] = 'STATUS MISMATCH';
                                    $log_data['status_comparison'] = array(
                                            'expected' => $expected_status,
                                            'actual' => $new_status
                                    );
                                }
                            } else {
                                $log_data['result'] = 'FAILED';
                                $log_data['reason'] = 'Status mapping NOT FOUND for payment method: ' . $orderPaymentStatus;
                            }
                        } else {
                            $log_data['result'] = 'FAILED';
                            $log_data['reason'] = 'VAHA <= 0';
                        }
                    } else {
                        $log_data['steps'][] = 'Rozmery > 0 - checking rozmery_data and vaha';

                        if ($rozmery_data != '' && $vaha > 0) {
                            $log_data['steps'][] = 'rozmery_data NOT empty AND vaha > 0';

                            if (isset($zasilkovna_send['status'][$orderPaymentStatus])) {
                                $expected_status = $zasilkovna_send['status'][$orderPaymentStatus];
                                $log_data['expected_status'] = $expected_status;

                                if ($expected_status == $new_status) {
                                    $log_data['steps'][] = 'STATUS MATCH! Sending ticket...';
                                    $log_data['result'] = 'SUCCESS';

                                    $ToretZasilkovna = ToretZasilkovnaLib();
                                    $ToretZasilkovna->Send->send_ticket($order_id, $package_count);

                                    zasilkovna_log(array(
                                            'order_id' => $order_id,
                                            'context' => __('Auto ticket sent successfully', TORETZASILKOVNASLUG),
                                            'log' => json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                    ));
                                    return;
                                } else {
                                    $log_data['result'] = 'FAILED';
                                    $log_data['reason'] = 'STATUS MISMATCH';
                                    $log_data['status_comparison'] = array(
                                            'expected' => $expected_status,
                                            'actual' => $new_status
                                    );
                                }
                            } else {
                                $log_data['result'] = 'FAILED';
                                $log_data['reason'] = 'Status mapping NOT FOUND for payment method: ' . $orderPaymentStatus;
                            }
                        } else {
                            $log_data['result'] = 'FAILED';
                            $log_data['reason'] = 'CONDITION FAILED';
                            $log_data['condition_check'] = array(
                                    'rozmery_data' => $rozmery_data,
                                    'rozmery_data_empty' => ($rozmery_data == ''),
                                    'vaha' => $vaha,
                                    'vaha_valid' => ($vaha > 0)
                            );
                        }
                    }
                } else {
                    $log_data['result'] = 'FAILED';
                    $log_data['reason'] = 'DEKLARACE is NOT "ne" - deklarace: ' . $deklarace;
                }
            } else {
                $log_data['result'] = 'FAILED';
                $log_data['reason'] = 'NOT Zasilkovna shipping';
            }
        } else {
            $log_data['result'] = 'FAILED';
            $log_data['reason'] = 'zasilkovna_send is EMPTY';
        }

        zasilkovna_log(array(
                'order_id' => $order_id,
                'context' => __('Auto ticket send attempt completed', TORETZASILKOVNASLUG),
                'log' => json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ));
    }

    function apply_free_shipping_coupon($rates)
    {
        $zasilkovna_option = get_option('zasilkovna_option', []);
        $zasilkovna_prices = get_option('zasilkovna_prices', []);

        $coupon_behaviour = $zasilkovna_option['free_coupon'] ?? 'all';

        if ($coupon_behaviour == 'none')
            return $rates;

        if (WC()->cart->has_discount()) {
            foreach (WC()->cart->get_coupons() as $code => $coupon) {

                if ($coupon->get_free_shipping()) {

                    foreach ($rates as $rate_id => $rate) {

                        if (tzas_is_zasilkovna_shipping($rate_id)) {

                            $service = tzas_get_service_from_string($rate_id);

                            if (tzas_is_native_method($service)) {
                                $slug = 'zasilkovna' . '-' . strtolower(toret_get_customer_country());
                            } else {
                                $slug = $service;
                            }

                            if ($coupon_behaviour == 'all' || ($coupon_behaviour == 'selected' && ($zasilkovna_prices[$slug . '-free-coupon'] ?? '') == 'ok')) {

                                $has_taxes = false;
                                $taxes = [];

                                foreach ($rate->taxes as $key => $tax) {
                                    if ($rate->taxes[$key] > 0) {
                                        $taxes[$key] = 0;
                                        $has_taxes = true;
                                    }
                                }

                                $rate->cost = 0;
                                $rate->taxes = [];
                                $rate->label = $rate->label . __(': Free', WOOZASILKOVNASLUG);

                                if ($has_taxes)
                                    $rate->taxes = $taxes;
                            }
                        }
                    }
                    break;
                }
            }
        }
        return $rates;
    }

    function flr_rates($rates)
    {
        $currencies = ['CZK', 'EUR', 'USD', 'GBP', 'PLN', 'HUF', 'RON'];
        $currency = self::get_current_currency();

        $zasilkovna_option = get_option('zasilkovna_option', []);
        $zasilkovna_prices = get_option('zasilkovna_prices', []);

        foreach ($rates as $rate_id => $rate) {
            if (!tzas_is_zasilkovna_shipping($rate_id)) {
                continue;
            }

            $service = tzas_get_service_from_string($rate_id);

            if (tzas_is_native_method($service)) {
                $slug = 'zasilkovna' . '-' . strtolower(toret_get_customer_country());
            } else {
                $slug = $service;
            }

            if (($zasilkovna_prices[$slug . '-flr-enabled'] ?? '') == 'ok' && in_array($currency, $currencies)) {
                $option = $slug . '-flr-' . $currency;
                $cost_from_db = $zasilkovna_prices[$option] ?? '';

                if ($cost_from_db !== '') {
                    $cost_incl_tax = (float)$cost_from_db;
                    $plugin_vat_option = $zasilkovna_option['price_with_vat'] ?? "";

                    if (get_option('woocommerce_calc_taxes') === 'yes' && $plugin_vat_option == 'ok' && class_exists('WC_Tax')) {

                        $tax_rates = WC_Tax::get_shipping_tax_rates();
                        $taxes = [];

                        if (!empty($tax_rates)) {
                            $taxes = WC_Tax::calc_tax($cost_incl_tax, $tax_rates, true);
                        }

                        $cost_excl_tax = $cost_incl_tax - array_sum($taxes);

                        $rate->cost = $cost_excl_tax;
                        $rate->set_taxes($taxes);

                    } else {
                        $rate->cost = $cost_incl_tax;
                        $rate->set_taxes([]);
                    }
                }
            }
        }
        return $rates;
    }

    public function get_current_currency()
    {
        global $WOOCS;
        if (!empty($WOOCS))
            return $WOOCS->current_currency;
        else {
            return get_woocommerce_currency();
        }

    }

    /**
     * Reset session data
     */
    public function reset_session($str, $order)
    {
        WC()->session->__unset('PacketaPointData');
        WC()->session->__unset('PacketaShippingData');
        WC()->session->__unset('addressSelected');

        return $str;
    }

}//End class

