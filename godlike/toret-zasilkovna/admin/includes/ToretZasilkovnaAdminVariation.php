<?php

defined('ABSPATH') || exit;

use ToretZasilkovna\Toret\Library\Settings;

if (!class_exists('ToretZasilkovnaAdminVariation')) {
    class ToretZasilkovnaAdminVariation
    {

        public function ZasilkovnaToretAddCustomFieldToVariations($loop, $variation_data, $variation): void
        {
            if (($variation_data['_virtual'][0] === 'yes') || ($variation_data['_downloadable'][0] === 'yes')) {
                return;
            }

            $ToretZasilkovna = ToretZasilkovnaLib();
            $zasilkovna_option = get_option('zasilkovna_option');
            $zasilkovna_services = get_option('zasilkovna_services');

            Settings::output_wrapper_start('variation');

            // --- 1. DISABLED METHODS (Top) ---
            $items_disabled = [];

            // Native Types
            if (!empty($zasilkovna_option['povolene_staty'])) {
                foreach ($zasilkovna_option['povolene_staty'] as $stat) {
                    if ($ToretZasilkovna->Helper->IsPacketaAviable(strtolower($stat))) {
                        foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                            $base_key = '_zasilkovna' . $native_type . '_' . strtolower($stat);
                            // Input name suffix: _vypnutivar, Meta key suffix: _vypnuti

                            $items_disabled[] = [
                                    'id'      => $base_key . '_vypnutivar_' . $variation->ID,
                                    'name'    => $base_key . '_vypnutivar[' . $loop . ']',
                                    'value'   => 'yes',
                                    'label'   => __('Pick-up points ' . $stat, WOOZASILKOVNASLUG),
                                    'checked' => get_post_meta($variation->ID, $base_key . '_vypnuti', true) === 'yes'
                            ];
                        }
                    }
                }
            }

            // Custom Services
            foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $service) {
                if (!empty($zasilkovna_services['service-active-' . $key])) {
                    $base_key = '_' . $key;
                    $label = !empty($zasilkovna_services['service-label-' . $key]) ? $zasilkovna_services['service-label-' . $key] : $service['nazev'];

                    $items_disabled[] = [
                            'id'      => $base_key . '_vypnutivar_' . $variation->ID,
                            'name'    => $base_key . '_vypnutivar[' . $loop . ']',
                            'value'   => 'yes',
                            'label'   => __($label, WOOZASILKOVNASLUG),
                            'checked' => get_post_meta($variation->ID, $base_key . '_vypnuti', true) === 'yes'
                    ];
                }
            }

            Settings::add_collapsible_checkboxes([
                    'title' => __('Check the Packeta shipping methods, which you want to disable for this product:', WOOZASILKOVNASLUG),
                    'items' => $items_disabled,
                    'type'  => 'top',
            ], 'div', false);


            // --- 2. FREE SHIPPING (Middle) ---
            $items_free = [];

            // Native Types
            if (!empty($zasilkovna_option['povolene_staty'])) {
                foreach ($zasilkovna_option['povolene_staty'] as $stat) {
                    if ($ToretZasilkovna->Helper->IsPacketaAviable(strtolower($stat))) {
                        foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                            $base_key = '_zasilkovna' . $native_type . '_' . strtolower($stat);

                            $items_free[] = [
                                    'id'      => $base_key . '_is_for_free_var_' . $variation->ID,
                                    'name'    => $base_key . '_is_for_free_var[' . $loop . ']',
                                    'value'   => 'yes',
                                    'label'   => __('Pick-up points ' . $stat, WOOZASILKOVNASLUG),
                                    'checked' => get_post_meta($variation->ID, $base_key . '_is_for_free', true) === 'yes'
                            ];
                        }
                    }
                }
            }

            // Custom Services
            foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $service) {
                if (!empty($zasilkovna_services['service-active-' . $key])) {
                    $base_key = '_' . $key;
                    $label = !empty($zasilkovna_services['service-label-' . $key]) ? $zasilkovna_services['service-label-' . $key] : $service['nazev'];

                    $items_free[] = [
                            'id'      => $base_key . '_is_for_free_var_' . $variation->ID,
                            'name'    => $base_key . '_is_for_free_var[' . $loop . ']',
                            'value'   => 'yes',
                            'label'   => __($label, WOOZASILKOVNASLUG),
                            'checked' => get_post_meta($variation->ID, $base_key . '_is_for_free', true) === 'yes'
                    ];
                }
            }

            Settings::add_collapsible_checkboxes([
                    'title' => __('Check the Packeta shipping methods, for which you want to have free shipping if product is in the cart:', WOOZASILKOVNASLUG),
                    'items' => $items_free,
                    'type'  => 'middle',
            ], 'div', false);


            // --- 3. DISABLE Z-BOXES (Bottom) ---
            $items_zbox = [];
            $items_zbox[] = [
                    'id'      => 'tzas_disable_var_zboxes_' . $variation->ID,
                    'name'    => 'tzas_disable_var_zboxes[' . $loop . ']',
                    'value'   => 'yes',
                    'label'   => __('Disable Z-BOXes', WOOZASILKOVNASLUG),
                    'checked' => get_post_meta($variation->ID, 'tzas_disable_var_zboxes', true) === 'yes'
            ];

            Settings::add_collapsible_checkboxes([
                    'title' => __('Disable pickup point types for this product:', WOOZASILKOVNASLUG),
                    'items' => $items_zbox,
                    'type'  => 'bottom',
            ], 'div', false);

            Settings::output_wrapper_end();
        }

        public function ZasilkovnaToretSaveCustomFieldVariations($variation_id, $i): void
        {
            $ToretZasilkovna = ToretZasilkovnaLib();
            $zasilkovna_option = get_option('zasilkovna_option');

            // Save Native Types
            if (!empty($zasilkovna_option['povolene_staty'])) {
                foreach ($zasilkovna_option['povolene_staty'] as $stat) {
                    if ($ToretZasilkovna->Helper->IsPacketaAviable(strtolower($stat))) {
                        foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                            $base_key = '_zasilkovna' . $native_type . '_' . strtolower($stat);

                            // Disabled
                            $input_key = $base_key . '_vypnutivar';
                            $meta_key  = $base_key . '_vypnuti';
                            if (isset($_POST[$input_key][$i])) {
                                update_post_meta($variation_id, $meta_key, 'yes');
                            } else {
                                delete_post_meta($variation_id, $meta_key);
                            }

                            // Free Shipping
                            $input_key = $base_key . '_is_for_free_var';
                            $meta_key  = $base_key . '_is_for_free';
                            if (isset($_POST[$input_key][$i])) {
                                update_post_meta($variation_id, $meta_key, 'yes');
                            } else {
                                delete_post_meta($variation_id, $meta_key);
                            }
                        }
                    }
                }
            }

            // Save Custom Services
            foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $service) {
                $base_key = '_' . $key;

                // Disabled
                $input_key = $base_key . '_vypnutivar';
                $meta_key  = $base_key . '_vypnuti';
                if (isset($_POST[$input_key][$i])) {
                    update_post_meta($variation_id, $meta_key, 'yes');
                } else {
                    delete_post_meta($variation_id, $meta_key);
                }

                // Free Shipping
                $input_key = $base_key . '_is_for_free_var';
                $meta_key  = $base_key . '_is_for_free';
                if (isset($_POST[$input_key][$i])) {
                    update_post_meta($variation_id, $meta_key, 'yes');
                } else {
                    delete_post_meta($variation_id, $meta_key);
                }
            }

            // Save Z-BOXes
            if (isset($_POST['tzas_disable_var_zboxes'][$i])) {
                update_post_meta($variation_id, 'tzas_disable_var_zboxes', 'yes');
            } else {
                update_post_meta($variation_id, 'tzas_disable_var_zboxes', 'no');
            }
        }
    }
}