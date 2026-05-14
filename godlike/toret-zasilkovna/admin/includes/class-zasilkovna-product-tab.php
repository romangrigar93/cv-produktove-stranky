<?php
defined('ABSPATH') || exit;

use ToretZasilkovna\Toret\Library\Settings;

if (!class_exists('Toret_Zasilkovna_Product_Tabs')) {
    class Toret_Zasilkovna_Product_Tabs
    {

        /**
         *  Add tab to product settings
         */
        public function add_zasilkovna_product_data_tab(array $product_data_tabs): array
        {
            $product_data_tabs['zasilkovna'] = array(
                    'label'  => __('Packeta', WOOZASILKOVNASLUG),
                    'target' => 'zasilkovna_product_data',
            );

            return $product_data_tabs;
        }


        /**
         *  Add fields to tab
         */
        public function add_zasilkovna_product_data_fields(): void
        {
            global $post;
            $post_id = $post->ID;

            $ToretZasilkovna = ToretZasilkovnaLib();
            $zasilkovna_option = get_option('zasilkovna_option');
            $zasilkovna_services = get_option('zasilkovna_services');

            ?>
            <div id="zasilkovna_product_data" class="panel woocommerce_options_panel">
                <?php
                Settings::output_wrapper_start('product');

                // --- 1. Age Verification ---
                echo '<div class="options_group">';
                woocommerce_wp_checkbox(array(
                        'id'          => '_zasilkovna_vek',
                        'label'       => __('Age verification', WOOZASILKOVNASLUG),
                        'description' => __('Tick if age verification is required for this product.', WOOZASILKOVNASLUG),
                        'default'     => '0',
                        'desc_tip'    => false,
                ));
                echo '</div>';

                // --- 2. Disabled Methods ---
                $items_disabled = [];

                // Native types (countries)
                if (!empty($zasilkovna_option['povolene_staty'])) {
                    foreach ($zasilkovna_option['povolene_staty'] as $stat) {
                        if ($ToretZasilkovna->Helper->IsPacketaAviable(strtolower($stat))) {
                            foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                                $field_id = '_zasilkovna' . $native_type . '_' . strtolower($stat) . '_vypnuti';
                                $items_disabled[] = [
                                        'id'      => $field_id,
                                        'name'    => $field_id,
                                        'value'   => 'yes',
                                        'label'   => __('Pick-up points ' . $stat, WOOZASILKOVNASLUG),
                                        'checked' => get_post_meta($post_id, $field_id, true) === 'yes'
                                ];
                            }
                        }
                    }
                }

                // Custom services
                foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $service) {
                    if (!empty($zasilkovna_services['service-active-' . $key])) {
                        $field_id = '_' . $key . '_vypnuti';
                        $label = !empty($zasilkovna_services['service-label-' . $key]) ? $zasilkovna_services['service-label-' . $key] : $service['nazev'];

                        $items_disabled[] = [
                                'id'      => $field_id,
                                'name'    => $field_id,
                                'value'   => 'yes',
                                'label'   => __($label, WOOZASILKOVNASLUG),
                                'checked' => get_post_meta($post_id, $field_id, true) === 'yes'
                        ];
                    }
                }

                Settings::add_collapsible_checkboxes([
                        'title'           => __('Check the Packeta shipping methods, which you want to disable for this product:', WOOZASILKOVNASLUG),
                        'items'           => $items_disabled,
                        'wrapper_classes' => '',
                        'type'            => 'middle'
                ], 'div', false);


                // --- 3. Free Shipping ---
                $items_free = [];

                // Native types
                if (!empty($zasilkovna_option['povolene_staty'])) {
                    foreach ($zasilkovna_option['povolene_staty'] as $stat) {
                        if ($ToretZasilkovna->Helper->IsPacketaAviable(strtolower($stat))) {
                            foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                                $field_id = '_zasilkovna' . $native_type . '_' . strtolower($stat) . '_is_for_free';
                                $items_free[] = [
                                        'id'      => $field_id,
                                        'name'    => $field_id,
                                        'value'   => 'yes',
                                        'label'   => __('Pick-up points ' . $stat, WOOZASILKOVNASLUG),
                                        'checked' => get_post_meta($post_id, $field_id, true) === 'yes'
                                ];
                            }
                        }
                    }
                }

                // Custom services
                foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $service) {
                    if (!empty($zasilkovna_services['service-active-' . $key])) {
                        $field_id = '_' . $key . '_is_for_free';
                        $label = !empty($zasilkovna_services['service-label-' . $key]) ? $zasilkovna_services['service-label-' . $key] : $service['nazev'];

                        $items_free[] = [
                                'id'      => $field_id,
                                'name'    => $field_id,
                                'value'   => 'yes',
                                'label'   => __($label, WOOZASILKOVNASLUG),
                                'checked' => get_post_meta($post_id, $field_id, true) === 'yes'
                        ];
                    }
                }

                Settings::add_collapsible_checkboxes([
                        'title'           => __('Check the Packeta shipping methods, for which you want to have free shipping if product is in the cart:', WOOZASILKOVNASLUG),
                        'items'           => $items_free,
                        'wrapper_classes' => '',
                        'type'            => 'middle'
                ], 'div', false);


                // --- 4. Disable Z-BOXes ---
                $items_zbox = [];
                $items_zbox[] = [
                        'id'      => 'tzas_disable_zboxes',
                        'name'    => 'tzas_disable_zboxes',
                        'value'   => 'yes',
                        'label'   => __('Disable Z-BOXes', WOOZASILKOVNASLUG),
                        'checked' => get_post_meta($post_id, 'tzas_disable_zboxes', true) === 'yes'
                ];

                Settings::add_collapsible_checkboxes([
                        'title'           => __('Disable pickup point types for this product:', WOOZASILKOVNASLUG),
                        'items'           => $items_zbox,
                        'wrapper_classes' => '',
                        'type'            => 'middle'
                ], 'div', false);


                // --- 5. Customs Declaration ---
                if (TORET_ZASILKOVNA_ENABLE_CUSTOMS) {
                    echo '<div class="options_group">';
                    echo '<p><b>' . __('Customs declaration', WOOZASILKOVNASLUG) . '</b></p>';

                    woocommerce_wp_text_input(array(
                            'id'    => '_zasilkovna_en_product_title',
                            'label' => __('Product name (EN)', WOOZASILKOVNASLUG),
                    ));

                    woocommerce_wp_text_input(array(
                            'id'    => '_zasilkovna_custom_code',
                            'label' => __('Customs code', WOOZASILKOVNASLUG),
                    ));
                    echo '</div>';
                }

                Settings::output_wrapper_end();
                ?>
            </div>
            <?php
        }

        /**
         *  Save tab to product settings
         */
        public function woocommerce_zasilkovna_fields_save(int $post_id): void
        {
            // Save Age Verification
            $woo_checkbox_vek = isset($_POST['_zasilkovna_vek']) ? 'yes' : 'no';
            if ($woo_checkbox_vek == 'yes') {
                update_post_meta($post_id, '_zasilkovna_vek', 'yes');
            } else {
                delete_post_meta($post_id, '_zasilkovna_vek');
            }

            // Save Z-BOX setting
            $woo_checkbox = isset($_POST['tzas_disable_zboxes']) ? 'yes' : 'no';
            if ($woo_checkbox == 'yes') {
                update_post_meta($post_id, 'tzas_disable_zboxes', $woo_checkbox);
            } else {
                delete_post_meta($post_id, 'tzas_disable_zboxes');
            }

            // Save Customs
            if (TORET_ZASILKOVNA_ENABLE_CUSTOMS) {
                if (isset($_POST['_zasilkovna_en_product_title'])) {
                    update_post_meta($post_id, '_zasilkovna_en_product_title', $_POST['_zasilkovna_en_product_title']);
                }
                if (isset($_POST['_zasilkovna_custom_code'])) {
                    update_post_meta($post_id, '_zasilkovna_custom_code', $_POST['_zasilkovna_custom_code']);
                }
            }

            // Save Dynamic Fields (Countries & Services)
            $ToretZasilkovna = ToretZasilkovnaLib();
            $zasilkovna_option = get_option('zasilkovna_option');
            $zasilkovnaKde = $ToretZasilkovna->Helper->zasilkovna_kde();

            if (!empty($zasilkovna_option['povolene_staty'])) {
                foreach ($zasilkovna_option['povolene_staty'] as $stat) {
                    // Check logic based on original method
                    if (in_array(strtolower($stat), $zasilkovnaKde)) {
                        foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                            // Disabled
                            $key_disabled = '_zasilkovna' . $native_type . '_' . strtolower($stat) . '_vypnuti';
                            if (isset($_POST[$key_disabled])) {
                                update_post_meta($post_id, $key_disabled, 'yes');
                            } else {
                                delete_post_meta($post_id, $key_disabled);
                            }

                            // Free Shipping
                            $key_free = '_zasilkovna' . $native_type . '_' . strtolower($stat) . '_is_for_free';
                            if (isset($_POST[$key_free])) {
                                update_post_meta($post_id, $key_free, 'yes');
                            } else {
                                delete_post_meta($post_id, $key_free);
                            }
                        }
                    }
                }
            }

            foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $service) {
                // Disabled
                $key_disabled = '_' . $key . '_vypnuti';
                if (isset($_POST[$key_disabled])) {
                    update_post_meta($post_id, $key_disabled, 'yes');
                } else {
                    delete_post_meta($post_id, $key_disabled);
                }

                // Free Shipping
                $key_free = '_' . $key . '_is_for_free';
                if (isset($_POST[$key_free])) {
                    update_post_meta($post_id, $key_free, 'yes');
                } else {
                    delete_post_meta($post_id, $key_free);
                }
            }
        }
    }
}