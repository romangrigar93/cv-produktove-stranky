<?php
global $wpdb;

use ToretZasilkovna\Toret\Library\Draw;

$draw = new Draw();
$localDraw = new ToretZasilkovnaDraw();

Toret_Admin_save::save_setting();
$ToretZasilkovna = ToretZasilkovnaLib();
$menu_items = tzas_get_admin_menu_items();

$tzas_license = get_option('woo-zasilkovna-licence');
$licence_key = get_option('woo-zasilkovna-licence-key');
$licence_info = get_option('woo-zasilkovna-info');
$zasilkovna_option = get_option('zasilkovna_option', []);
$zasilkovna_send = get_option('zasilkovna_send', []);
$zasilkovna_services = get_option('zasilkovna_services', []);
$country_currency = get_option('zasilkovna_country_currency', []);
$zasilkovna_prices = get_option('zasilkovna_prices', []);

$statusesZasilkovna = $ToretZasilkovna->Helper->zasilkovna_statuses();
$orderStatuses = wc_get_order_statuses();
$orderStatusesAdjusted = tzas_adjust_order_statuses_slugs();

$gatewaysModified = array();
$all_gateways = WC()->payment_gateways();
foreach ($all_gateways as $keys => $items) {
    foreach ($items as $key => $item) {
        $gatewaysModified[$item->id] = $item->get_title();
    }
}

$site_url = tzas_get_site_url();

?>
<div class="wrap toret-admin-wrap">

    <?php
    foreach ($menu_items as $menu_item => $menu_title) {
        if (isset($_GET['form']) && $_GET['form'] == $menu_item . '-settings') {
            if (($_GET['form'] == 'carriers-settings') && (isset($_GET['country']) && $_GET['country'] != '')) {
                echo '<h1 class="toret-admin-title">' . __('Country settings', WOOZASILKOVNASLUG) . ' - ' . $_GET['country'] . '</h1>';
            } else {
                echo '<h1 class="toret-admin-title">' . $menu_title . '</h1>';
            }
            break;
        }
    }
    ?>

    <div class="toret-admin-main-wrap">

        <div class="toret-menu-wrap">
            <ul class="toret-menu">
                <?php
                foreach ($menu_items as $menu_item => $menu_title) {
                    $menu_slug = $menu_item . '-settings';
                    if ($menu_item != 'general' && $tzas_license != 'active') {
                        continue;
                    }

                    $active = '';
                    if ($menu_item == 'general' && !isset($_GET['form'])) {
                        $active = 'active';
                    } else {
                        $active = $this->get_active($menu_slug);
                    }

                    $url = admin_url(TORETZASILKOVNASETTINGS . '&form=' . $menu_slug);
                    ?>
                    <li>
                        <a href="<?php echo $url; ?>"
                           class="<?php echo $active; ?>">
                            <?php echo $menu_title; ?>
                        </a>
                    </li>
                    <?php
                }

                ?>

            </ul>
        </div>

        <?php
        if (in_array($this->get_form(), array_keys($menu_items))) {
            if ((isset($_GET['form']) && $_GET['form'] == 'carriers-settings') && (isset($_GET['country']) && $_GET['country'] != '')) {
                include('parts/country.php');
            } else {
                include('parts/' . $this->get_form() . '.php');
            }
        }
        ?>

    </div>
    <div class="clear"></div>
    <button id="to-top-button"
            title="<?php _e('Go to top', WOOZASILKOVNASLUG); ?>"><span class="dashicons dashicons-arrow-up-alt"></span>
    </button>
</div>
