<?php

defined('ABSPATH') || exit;

use ToretZasilkovna\Toret\Library\Settings;

class ToretZasilkovnaProductCategory
{
    public function wh_taxonomy_add_new_meta_field(): void
    {
        _e('Packeta', WOOZASILKOVNASLUG);
        $this->render_ui_wrapper();
    }

    public function wh_taxonomy_edit_meta_field(object $term): void
    {
        $term_id = $term->term_id;
        ?>
        <tr class="form-field term-display-type-wrap">
            <th scope="row" valign="top"><label><?php _e('Packeta', WOOZASILKOVNASLUG); ?></label></th>
            <td>
                <?php $this->render_ui_wrapper($term_id); ?>
            </td>
        </tr>
        <?php
    }

    private function render_ui_wrapper(?int $term_id = null): void
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $zasilkovna_option = get_option('zasilkovna_option');
        $zasilkovna_services = get_option('zasilkovna_services');

        Settings::output_wrapper_start('category');

        // --- 1. DISABLED METHODS ---
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
                                'checked' => $term_id && get_term_meta($term_id, $field_id, true) === 'yes'
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
                        'checked' => $term_id && get_term_meta($term_id, $field_id, true) === 'yes'
                ];
            }
        }

        Settings::add_collapsible_checkboxes([
                'title' => __('Check the Packeta shipping methods, which you want to disable for this category:', WOOZASILKOVNASLUG),
                'items' => $items_disabled,
                'type'  => 'top',
        ], 'div', false);


        // --- 2. FREE SHIPPING METHODS ---
        $items_free = [];

        // Native types (countries)
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
                                'checked' => $term_id && get_term_meta($term_id, $field_id, true) === 'yes'
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
                        'checked' => $term_id && get_term_meta($term_id, $field_id, true) === 'yes'
                ];
            }
        }

        Settings::add_collapsible_checkboxes([
                'title' => __('Check the Packeta shipping methods, for which you want to have free shipping if category is in the cart:', WOOZASILKOVNASLUG),
                'items' => $items_free,
                'type'  => 'middle',
        ], 'div', false);


        // --- 3. DISABLE Z-BOXES ---
        $items_zbox = [];
        $items_zbox[] = [
                'id'      => 'tzas_disable_cat_zboxes',
                'name'    => 'tzas_disable_cat_zboxes',
                'value'   => 'yes',
                'label'   => __('Disable Z-BOXes', WOOZASILKOVNASLUG),
                'checked' => $term_id && get_term_meta($term_id, 'tzas_disable_cat_zboxes', true) === 'yes'
        ];

        Settings::add_collapsible_checkboxes([
                'title' => __('Disable pickup point types for this category:', WOOZASILKOVNASLUG),
                'items' => $items_zbox,
                'type'  => 'bottom',
        ], 'div', false);

        Settings::output_wrapper_end();
    }

    public function wh_save_taxonomy_custom_meta(int $term_id): void
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $zasilkovna_option = get_option('zasilkovna_option');

        // Save Native Types
        if (!empty($zasilkovna_option['povolene_staty'])) {
            foreach ($zasilkovna_option['povolene_staty'] as $stat) {
                if ($ToretZasilkovna->Helper->IsPacketaAviable(strtolower($stat))) {
                    foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
                        // Disabled
                        $key_disabled = '_zasilkovna' . $native_type . '_' . strtolower($stat) . '_vypnuti';
                        if (isset($_POST[$key_disabled])) {
                            update_term_meta($term_id, $key_disabled, 'yes');
                        } else {
                            delete_term_meta($term_id, $key_disabled);
                        }

                        // Free Shipping
                        $key_free = '_zasilkovna' . $native_type . '_' . strtolower($stat) . '_is_for_free';
                        if (isset($_POST[$key_free])) {
                            update_term_meta($term_id, $key_free, 'yes');
                        } else {
                            delete_term_meta($term_id, $key_free);
                        }
                    }
                }
            }
        }

        // Save Custom Services
        foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $service) {
            // Disabled
            $key_disabled = '_' . $key . '_vypnuti';
            if (isset($_POST[$key_disabled])) {
                update_term_meta($term_id, $key_disabled, 'yes');
            } else {
                delete_term_meta($term_id, $key_disabled);
            }

            // Free Shipping
            $key_free = '_' . $key . '_is_for_free';
            if (isset($_POST[$key_free])) {
                update_term_meta($term_id, $key_free, 'yes');
            } else {
                delete_term_meta($term_id, $key_free);
            }
        }

        // Save Z-BOX setting
        if (isset($_POST['tzas_disable_cat_zboxes'])) {
            update_term_meta($term_id, 'tzas_disable_cat_zboxes', 'yes');
        } else {
            $val = isset($_POST['tzas_disable_cat_zboxes']) ? 'yes' : 'no';
            update_term_meta($term_id, 'tzas_disable_cat_zboxes', $val);
        }
    }
}