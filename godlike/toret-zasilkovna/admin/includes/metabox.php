<?php

use ToretZasilkovna\Toret\Library\Dimensions;

/**
 * metabox html
 *
 */
function order_zasilkovna_log_meta_box($post_or_order_object)
{
    $order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;


    /*$zasilkovna_option = get_option('zasilkovna_option');
    $ToretZasilkovna = ToretZasilkovnaLib();
    $weight = ToretZasilkovnaDimensionHelper::get_zasilkovna_weight($order->get_id());
    $max_dim = Dimensions::get_order_max_dimension($order, false, true);
    $max_dim_sum = Dimensions::get_order_max_sides_sum($order, false, true);
    $multipackage_data = $ToretZasilkovna->Helper->get_multipackage_data($zasilkovna_option, $weight, $max_dim, $max_dim_sum);
    var_error_log($multipackage_data);*/

    $order_id = $order->get_id();
    $id_zasilky = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky');
    $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');

    if (empty($zasilkovna_shipping)) {
        $zasilkovna_shipping = tzas_get_shipping_method_id($order);
    }

    echo '<div class="torlib-metabox">';

    $country = $order->get_shipping_country();
    if (empty($country))
        $country = $order->get_shipping_country();

    if (empty($country)) {
        $country = get_option('woocommerce_default_country');
    }

    if (empty($id_zasilky)) {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $komplet_data = $ToretZasilkovna->Helper->komplet_data();
        $zasilkovna_services = get_option('zasilkovna_services');

        $select = '<div class="torlib-metabox-input-wrap"><label for="tzas-change-method">' . __('Change shipping method to:', 'zasilkovna') . '</label><select id="tzas-change-method" name="tzas-change-method">';
        $select .= '<option value="-">' . __('---', WOOZASILKOVNASLUG) . '</option>';

        $vypsano = array();
        foreach ($komplet_data as $key => $service) {
            $aviable = $ToretZasilkovna->Helper->IsPacketaAviableAdmin($service['stat']);
            if ($aviable) {
                if (!in_array($service['stat'], $vypsano)) {
                    $vypsano[] = $service['stat'];
                    $stat = strtolower($service['stat']);

                    if ($service['stat'] == $country) {
                        foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                            if (!empty($zasilkovna_services['vydejnimista' . $native_type . '-active' . $stat]) ? 'checked="checked"' : '')
                                $select .= '<option value="zasilkovna>' . TORET_ZASILKOVNA_NATIVE_SHIPPINGS[$native_type] . '">' . (!empty($zasilkovna_services['vydejnimista' . $native_type . $stat]) ? $zasilkovna_services['vydejnimista' . $native_type . $stat] : tzas_get_native_label($native_type)) . '</option>';
                        }
                    }
                }
            }
            if (!empty($zasilkovna_services['service-active-' . $key]) ? 'checked="checked"' : '')
                if ($service['stat'] == $country)
                    $select .= '<option value="' . $service['prac'] . '">' . (!empty($zasilkovna_services['service-label-' . $key]) ? $zasilkovna_services['service-label-' . $key] : $service['nazev']) . '</option>';
        }
        $select .= '</select>';
        $select .= '<button style="margin-top:5px" data-orderid="' . $order_id . '" data-country="' . $country . '"  type="button" class="button" value="' . __('Change shipping', WOOZASILKOVNASLUG) . '" id="zasilkovna-change-shipping"><span class="dashicons dashicons-car"></span>'.__('Change shipping', WOOZASILKOVNASLUG).'</button>';
        $select .= '</div>';
        echo $select;
    }

    if (tzas_is_zasilkovna_shipping($zasilkovna_shipping)) {
        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');
        $ToretZasilkovna = ToretZasilkovnaLib();

        $id_baliku_dopravce = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce');

        $disabled = $ToretZasilkovna->Helper->DisableByDim(str_replace('zasilkovna>', '', $zasilkovna_shipping), $order->get_shipping_country(), false, $order);

        $rozmery = 0;
        if ($zasilkovna_shipping) {
            $komplet_data = $ToretZasilkovna->Helper->komplet_data();
            foreach ($komplet_data as $data) {
                if ($data['prac'] == $zasilkovna_shipping) {
                    $rozmery = $data['rozmery'];
                }
            }
        }

        if ($rozmery > 0) {
            $Dimensions = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension', true);
            $Dim = explode('|', $Dimensions);
        }

        $oversized = '<span class="tzas-oversized-column dashicons dashicons-warning" title="' . __('This package is probably oversized.', 'zasilkovna') . '"></span>';
        ?>

        <p class="zasilkovna-metabox-input">
            <label for="zasilkovna-weight"><?php _e('Shipment weight', WOOZASILKOVNASLUG); ?></label>
            <input type="number" step="any" min="0.001" name="zasilkovna-weight" id="zasilkovna-weight"
                   value="<?php echo(Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_weight', true) ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_weight', true) : ''); ?>"
                   placeholder="<?php echo get_option('woocommerce_weight_unit'); ?>"/>
        </p>


        <p class="zasilkovna-metabox-input">
            <label for="zasilkovna-total"><?php _e('Order total', WOOZASILKOVNASLUG); ?></label>
            <input type="number" step="any" min="0.00001" name="zasilkovna-total" id="zasilkovna-total"
                   value="<?php echo(Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_total', true) ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_total', true) : ''); ?>"
                   placeholder="<?php echo get_woocommerce_currency_symbol(); ?>"/>
        </p>


        <p class="zasilkovna-metabox-input">
            <label for="zasilkovna-dim-one"><?php _e('Maximum size of one side [m]', WOOZASILKOVNASLUG); ?><?php echo($disabled && $id_zasilky == '' ? $oversized : ''); ?></label>
            <input type="number" step="any" min="0.00001" name="zasilkovna-dim-one" id="zasilkovna-dim-one"
                   value="<?php echo(Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dim_one', true) != '' ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dim_one', true) : ''); ?>"
                   placeholder="<?php echo 'm'; ?>"/>
        </p>


        <p class="zasilkovna-metabox-input">
            <label for="zasilkovna-dim-sum"><?php _e('Sum of all three sides [m]', WOOZASILKOVNASLUG); ?><?php echo($disabled && $id_zasilky == '' ? $oversized : ''); ?></label>
            <input type="number" step="any" min="0.00001" name="zasilkovna-dim-sum" id="zasilkovna-dim-sum"
                   value="<?php echo(Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dim_sum', true) != '' ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dim_sum', true) : ''); ?>"
                   placeholder="<?php echo 'm'; ?>"/>
        </p>

        <?php

        if ($rozmery > 0) { ?>
            <p class="zasilkovna-metabox-input">
                <label for="zasilkovna-width"><?php _e('Shipment width', WOOZASILKOVNASLUG); ?> [mm]</label>
                <input type="number" step="1" min="10" name="zasilkovna-width" id="zasilkovna-width"
                       value="<?php echo(isset($Dim[0]) ? $Dim[0] : ''); ?>" placeholder="mm"/>
            </p>
            <p class="zasilkovna-metabox-input">
                <label for="zasilkovna-height"><?php _e('Shipment height', WOOZASILKOVNASLUG); ?> [mm]</label>
                <input type="number" step="1" min="10" name="zasilkovna-height" id="zasilkovna-height"
                       value="<?php echo(isset($Dim[1]) ? $Dim[1] : ''); ?>" placeholder="mm"/>
            </p>
            <p class="zasilkovna-metabox-input">
                <label for="zasilkovna-lenght"><?php _e('Shipment length', WOOZASILKOVNASLUG); ?> [mm]</label>
                <input type="number" step="1" min="10" name="zasilkovna-lenght" id="zasilkovna-lenght"
                       value="<?php echo(isset($Dim[2]) ? $Dim[2] : ''); ?>" placeholder="mm"/>
            </p>
        <?php }

        $location = $ToretZasilkovna->Helper->get_action_current_location($order, true);

        if (empty($id_zasilky)) {
            $ToretZasilkovna->Helper->draw_empty_package_actions($order_id, $ToretZasilkovna, true);
        } else {
            $zasilkovna_option = get_option('zasilkovna_option');
            if (!empty($zasilkovna_option['disable_popup_print']) && $zasilkovna_option['disable_popup_print'] == 'ok') {
                $popupPrint = 'disabled-popup';
            } else {
                $popupPrint = '';
            }

            echo '<a href="' . $location . '&zasilkovna_order_id=' . $order_id . '&zasilkovna_ticket_id=' . $id_zasilky . ($id_baliku_dopravce != '' ? '&id_baliku_dopravce=' . $id_baliku_dopravce : '') . /*($id_dopravy != '' ? '&id_dopravy=' . $id_dopravy : '') .*/ '" class="' . $popupPrint . ' button download-label toret-print-target torlib-metabox-action-button' . $order_id . ' toret-print-target' . $order_id . '" data-id="' . $order_id . '"><span class="dashicons dashicons-pdf" title="' . __('Download label', 'zasilkovna') . '"></span>' . __('Download label', 'zasilkovna') . '</a>';

            do_action('toret_detail_action', $order_id);
            if (!empty($zasilkovna_option['asistent']) && $zasilkovna_option['asistent'] == 'ok') {
                $id_zasilky_assistent = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_assistent', true);

                if (!empty($id_zasilky_assistent)) {
                    echo '<a href="' . $location . '&zasilkovna_order_id=' . $order_id . '&zasilkovna_ticket_id_assistent=' . $id_zasilky_assistent . '" class="button torlib-metabox-action-button"><span class="dashicons dashicons-controls-repeat"></span>' . __('Claim assistant label', 'zasilkovna') . '</a>';
                } else {
                    echo '<a href="' . $location . '&zasilkovna_id_objednavky_assistent=' . $order_id . '" class="button torlib"><span class="dashicons dashicons-admin-users"></span>' . __('Claim assistant', 'zasilkovna') . '</a>';
                }
            }

            echo '<a href="' . $location . '&zasilkovna_delete=' . $order_id . '" class="button zasilkovna-delete-package torlib-metabox-action-button" data-orderid="' . $order_id . '" data-claim="yes"><span class="dashicons dashicons-table-col-delete"></span>' . __('Delete all package data', 'zasilkovna') . '</a>';

            if (!empty($id_zasilky_assistent)) {
                echo '<a href="' . $location . '&zasilkovna_cancel=' . $order_id . '&is_claim=yes" class="button zasilkovna-cancel-package torlib-metabox-action-button " data-orderid="' . $order_id . '" data-claim="yes"><span class="dashicons dashicons-no"></span>' . __('Cancel claim package', 'zasilkovna') . '</a>';
            }

            echo '<a href="' . $location . '&zasilkovna_cancel=' . $order_id . '&is_claim=no" class="button zasilkovna-cancel-package torlib-metabox-action-button " data-orderid="' . $order_id . '" data-claim="no" ><span class="dashicons dashicons-trash"></span>' . __('Cancel package', 'zasilkovna') . '</a>';
        }

        ?>
        <hr>
        <p><a href="<?php echo admin_url() . 'admin.php?page=zasilkovna-log&order_id=' . $order_id; ?>"
              target="_blank"><?php _e('Show log entries for this order', WOOZASILKOVNASLUG); ?></a></p>
        <?php

    }

    echo '</div>';
}