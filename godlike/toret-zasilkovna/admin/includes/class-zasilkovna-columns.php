<?php
defined('ABSPATH') || exit;

/**
 * Order review functions
 */
if (!class_exists('Toret_Zasilkovna_Columns')) {
    class Toret_Zasilkovna_Columns
    {

        /**
         * Add column to orders review
         */
        public function barcode_column(array $columns): array
        {
            $new_columns = array_map(function ($item) {
                return $item;
            }, $columns);

            $new_columns['zasilkovna_send'] = __('Packeta', WOOZASILKOVNASLUG);
            $new_columns['zasilkovna_status'] = __('Packeta status', WOOZASILKOVNASLUG);

            return $new_columns;
        }

        /**
         * Display send button and barcode
         */
        public function barcode_column_display($column_name, $post_id): void
        {
            switch ($column_name) {
                /*case 'zasilkovna' :
                    self::column_zasilkovna( $post_id );
                    break;*/
                case 'zasilkovna_send' :
                    self::column_zasilkovna_send($post_id);
                    break;
                case 'zasilkovna_status' :
                    self::column_zasilkovna_status($post_id);
                    break;
            }
        }

        /**
         * Add column to orders review
         */
        private function column_zasilkovna_status($post_id): void
        {

            $order = wc_get_order($post_id);
            $order_id = $order->get_id();

            $statuses = explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_order_status', true));

            $text = '';
            $i = 1;
            foreach ($statuses as $status) {
                if ($status != '') {
                    $stavy = ToretZasilkovnaHelper::zasilkovna_statuses();
                    if (isset($stavy[$status]))
                        $text .= ($i != 1 ? ', ' : '') . $stavy[$status];
                }
                $i++;
            }
            echo $text;
        }

        /**
         * Add column to orders review with icons
         */
        private function column_zasilkovna_send($post_id): void
        {
            $order = wc_get_order($post_id);
            $order_id = $order->get_id();

            $ToretZasilkovna = ToretZasilkovnaLib();
            $location = $ToretZasilkovna->Helper->get_action_current_location($order, false);

            $zasilkovna_id = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_pobocky');

            if (!empty($zasilkovna_id)) {

                $zasilkovna_option = get_option('zasilkovna_option', array());

                $id_zasilky = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky');
                $id_baliku_dopravce = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce');

                echo '<div class="torlib-column-buttons">';
                $ToretZasilkovna->Helper->draw_empty_package_actions($order_id, $ToretZasilkovna, false);

                if (!empty($id_zasilky)) {
                    if (!empty($zasilkovna_option['disable_popup_print']) && $zasilkovna_option['disable_popup_print'] == 'ok') {
                        $popupDisable = '&offset=0&format=';
                        if ($id_baliku_dopravce != '') {
                            $popupDisable .= $zasilkovna_option['packeta_services_default_status'] ?? 'A6-on-A4';
                        } else {
                            $popupDisable .= $zasilkovna_option['packeta_default_status'] ?? 'A6-on-A4';
                        }
                    } else {
                        $popupDisable = '';
                    }

                    echo '<a href="' . $location . '&zasilkovna_order_id=' . $order_id . '&zasilkovna_ticket_id=' . $id_zasilky . ($id_baliku_dopravce != '' ? '&id_baliku_dopravce=' . $id_baliku_dopravce : '') . $popupDisable . '" class="button ' . ($popupDisable == '' ? 'download-label' : '') . ' toret-print-target' . $order_id . ' torlib-column-action-button" data-id="' . $order_id . '"><span class="dashicons dashicons-pdf" title="' . __('Print label', 'zasilkovna') . '"></span></a>';


                    echo '<div class="';
                    if (empty($zasilkovna_option['disable_popup_print']) || $zasilkovna_option['disable_popup_print'] != 'ok') {
                        echo 'toret-print-' . $order_id . ' toret-popup-print toret-single-print';
                    }
                    echo '" style="display:none;">
                            <div class="toret-popup-inner toret-popup-print-inner">
                                <h2 class="toret-popup-title">' . __('Printing settings', 'zasilkovna') . '</h2>
                                <label class="toret-popup-label">' . __('Format:', 'zasilkovna') . '
                                    <select class="toret-format toret-format' . $order_id . '" data-id="' . $order_id . '">';


                    if (($id_baliku_dopravce != '')) {
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
                               <label class="toret-popup-label">' . __('Offset:', 'zasilkovna') . '<input type="number" min="0" step="1" value="0" class="toret-input-offset toret-input-offset' . $order_id . '" /></label>
                                 <div class="toret-popup-print-buttons">
                                <button class="tzas-ulozit toret-popup-print-close" data-id="' . $order_id . '">' . __('Close', 'zasilkovna') . '</button>
                                <button class="tzas-ulozit toret-popup-print-save" data-id="' . $order_id . '">' . __('Print', 'zasilkovna') . '</button>
                                </div>                         
                            </div>
                        </div>';


                    $fields = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode', true);
                    $fields = explode(';', $fields);
                    $html = '';
                    $i = 0;

                    $order = wc_get_order($order_id);

                    $locale = $ToretZasilkovna->Helper->get_language_by_country($order->get_shipping_country());

                    foreach ($fields as $field) {
                        if ($field != '') {
                            $html .= '<a href="https://tracking.packeta.com/' . $locale . '/?id=' . $field . '" target="_blank" class="button torlib-column-action-button"><span class="dashicons dashicons-search" title="' . __('Track package: ', 'zasilkovna') . $field . '"></span></a>';
                        }
                        $i++;
                    }

                    echo $html;
                    echo '<a href="' . $location . '&zasilkovna_cancel=' . $order_id . '&is_claim=no" data-orderid="' . $order_id . '" data-claim="no" class="button zasilkovna-cancel-package torlib-column-action-button "><span class="dashicons dashicons-trash" title="' . __('Cancel package', 'zasilkovna') . '"></span></a>';
                    echo '</div>';
                }
            }
        }
    }
}