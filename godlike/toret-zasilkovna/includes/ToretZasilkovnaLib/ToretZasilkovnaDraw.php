<?php
/**
 * WooCommerce Meta Box Functions
 *
 * @author      WooThemes
 * @category    Core
 * @package     WooCommerce\Admin\Functions
 * @version     2.3.0
 */

use ToretZasilkovna\Toret\Library\Draw;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ToretZasilkovnaDraw
{
    static function draw_carrier_settings($carrier, $stat, $zasilkovna_services, $zasilkovna_option, $zasilkovna_prices)
    {
        $draw = new Draw();

        $showMainSaveButton = true;

        $type = '';
        $limitations = [];
        $show_cod_settings = true;


        if ($carrier['type'] == 'native') {
            $slug = 'zasilkovna' . $type . '-' . $stat;
            $slug_label = 'vydejnimista' . $type . $stat;
            $slug_active = 'vydejnimista' . $type . '-active' . $stat;
            $zasvl = 'zasilkovna' . $type . '-vl';
            $table_slug = 'zasilkovna' . $type;
            $max_weight_info = '15';
            $slug_cod = 'zasilkovna' . $type;
            $zasvlfee = 'zasilkovna' . $type . '-vlfee';
            $icon_option = 'icon_url_' . $stat;
            $icon_select_option = 'icon_select_url_' . $stat;
        } else {
            $ToretZasilkovna = ToretZasilkovnaLib();
            $service_data = $ToretZasilkovna->Helper->GetServiceBySlug($carrier['slug']);
            $slug = $service_data['slug'];
            $key = $service_data['id'];
            $slug_label = 'service-label-' . $key;
            $slug_active = 'service-active-' . $key;
            $zasvl = $slug . 'vl';
            $table_slug = $slug;
            $max_weight_info = $service_data['vaha'];
            $slug_cod = $service_data['slug'];
            $zasvlfee = $slug;
            $icon_option = 'icon_url_' . $slug;

            if (isset($service_data['pobocky']) && $service_data['pobocky'] == 1) {
                $icon_select_option = 'icon_select_url_' . $slug;
            }

            if ($service_data['dobirka'] != 0) {
                $show_cod_settings = false;
            }

            $hidden_for_api = (isset($service_data['api']) && $service_data['api'] != '1');
            if ($hidden_for_api || $service_data['deklarace'] > 0) {
                $limitations[] = __('A package for this carrier cannot be sent through the Packeta API. It must be added manually.', WOOZASILKOVNASLUG);
            }

            if ($service_data['rozmery'] > 0 && !$hidden_for_api) {
                $limitations[] = __('The carrier requires information about the package dimensions before send throught the Packeta API.', WOOZASILKOVNASLUG) . '</td></tr>';
            }
        }
        $is_active = !empty($zasilkovna_services[$slug_active]);

        $saved_type = tzas_get_rate_fee_type($zasilkovna_option, $zasilkovna_prices, $slug);
        $saved_type_cod = tzas_get_cod_fee_type($zasilkovna_option, $zasilkovna_prices, $slug);

        $display = ($is_active ? '' : 'style="display:none"');

        $label = (!empty($zasilkovna_services[$slug_label]) ? $zasilkovna_services[$slug_label] : $carrier['original_title']);

        $html = '';

        if (!empty($limitations)) {
            foreach ($limitations as $limitation) {
                $html .= '<tr>';
                $html .= '<th class="titledesc zasilkovna-pozadavek" colspan="2"><span>' . $limitation . '</span></th>';
                $html .= '<tr>';
            }
        }

        /**
         *
         */
        $html .= $draw::add_checkbox(
            array(
                'id' => $slug_active,
                'value' => $zasilkovna_services[$slug_active] ?? "",
                'label' => __('Active carrier', WOOZASILKOVNASLUG)
            )
        );

        if ($is_active) {

            $html .= $draw::add_text(
                array(
                    'id' => $slug_label,
                    'placeholder' => '',
                    'type' => 'text',
                    'label' => __('Shipping name on the checkout page', WOOZASILKOVNASLUG),
                    'value' => $label,
                )
            );

            /**
             *
             */
            $html .= '<tr>';
            $html .= '<th scope="row" class="titledesc"><label for="' . $slug . '-weight-type">' . __('Shipping rate calculation type', WOOZASILKOVNASLUG) . '</label></th>';
            $html .= '<td class="forminp forminp-text">';
            $html .= '<select id="' . $slug . '-weight-type" class="select short tzas-weight-type-selection toret-level-selection" data-single="' . $slug . '-celk' . '" data-alltables="' . $zasvl . 'pridat-' . $stat . '"  name="' . $slug . '-weight-type">';
            $html .= '<option data-target="' . $zasvl . 'none' . 'pridat-' . $stat . '" value="single"' . ($saved_type == 'single' ? 'selected="selected"' : '') . '>' . __('Flat rate', WOOZASILKOVNASLUG) . '</option>';
            $html .= '<option data-target="' . $zasvl . 'hm' . 'pridat-' . $stat . '" value="weight"' . ($saved_type == 'weight' ? 'selected="selected"' : '') . '>' . __('Weight-based', WOOZASILKOVNASLUG) . '</option>';
            $html .= '<option data-target="' . $zasvl . 'dm' . 'pridat-' . $stat . '" value="dimension"' . ($saved_type == 'dimension' ? 'selected="selected"' : '') . '>' . __('Dimension-based', WOOZASILKOVNASLUG) . '</option>';
            $html .= '<option data-target="' . $zasvl . 'pr' . 'pridat-' . $stat . '" value="price"' . ($saved_type == 'price' ? 'selected="selected"' : '') . '>' . __('Total price-based', WOOZASILKOVNASLUG) . '</option>';
            $html .= '</select>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= $draw::add_text(
                array(
                    'id' => $slug . '-celk',
                    'placeholder' => '',
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.0001',
                        'min' => '0',
                    ),
                    'row_class' => array($slug . '-celk'),
                    'label' => __('Unified price', WOOZASILKOVNASLUG),
                    'description' => __('The flat rate applies when no restrictions are set based on the weight, dimensions, or total price of the order.', WOOZASILKOVNASLUG),
                    'default' => '',
                    'value' => $zasilkovna_prices[$slug . '-celk'] ?? "",
                )
            );

            $option = $slug . '-flr-' . 'enabled';
            $html .= $draw::add_checkbox(
                array(
                    'id' => $option,
                    'value' =>  $zasilkovna_prices[$option] ?? "",
                    'label' => __('Flat rate in other currencies', WOOZASILKOVNASLUG),
                    'cbvalue' => 'ok',
                    'class' => 'tzas-flr-checkbox',
                    'custom_attributes' => array(
                        'data-target' => 'tzas' . '-flr-' . $slug,
                    )
                )
            );

            $currencies = ['CZK','EUR', 'USD', 'GBP', 'PLN', 'HUF', 'RON'];
            $currencies = apply_filters('tzas_foreign_currencies', $currencies);
            foreach ($currencies as $currency) {
                $option = $slug . '-flr-' . $currency;
                $html .= $draw::add_text(
                    array(
                        'id' => $option,
                        'placeholder' => '',
                        'label' => __('Flat rate - ', WOOZASILKOVNASLUG) . $currency,
                        'value' => $zasilkovna_prices[$option] ?? "",
                        'type' => 'number',
                        'custom_attributes' => array(
                            'step' => '0.000001',
                            'min' => '0',
                        ),
                        'wrapper_class' => 'tzas' . '-flr-' . $slug
                    )
                );
            }


            $html .= '<tr>';
            $html .= '<th></th>';
            $html .= '<td>';

            //
            $headers = array(
                __('Weight ABOVE', WOOZASILKOVNASLUG),
                __('Weight UP TO', WOOZASILKOVNASLUG),
                __('Rate ', WOOZASILKOVNASLUG)
            );
            $add_string = __('Add weight limit', WOOZASILKOVNASLUG);
            $delete_string = __('Delete', WOOZASILKOVNASLUG);
            $title = __('Set prices based on weight', WOOZASILKOVNASLUG);
            $table_type = 'weight';
            $display_type = ($saved_type == $table_type ? 'style="display:table;"' : 'style="display:none"');
            $displayAdd = ($saved_type == $table_type);
            $html .= self::draw_limit_table($zasvl, $table_slug, '', 'hm', $headers, $title, $add_string, $delete_string, $zasilkovna_option, $zasilkovna_prices, $stat, $display, $display_type, $type,$displayAdd);


            //
            $headers = array(
                __('Dimension [m] ABOVE', WOOZASILKOVNASLUG),
                __('Dimension [m]  UP TO', WOOZASILKOVNASLUG),
                __('Rate ', WOOZASILKOVNASLUG)
            );
            $add_string = __('Add dimension limit', WOOZASILKOVNASLUG);
            $delete_string = __('Delete', WOOZASILKOVNASLUG);
            $title = __('Set prices based on max dimension', WOOZASILKOVNASLUG);
            $table_type = 'dimension';
            $display_type = ($saved_type == $table_type ? 'style="display:table;"' : 'style="display:none"');
            $displayAdd = ($saved_type == $table_type);
            $html .= self::draw_limit_table($zasvl, $table_slug, 'dm', 'dm', $headers, $title, $add_string, $delete_string, $zasilkovna_option, $zasilkovna_prices, $stat, $display, $display_type, $type,$displayAdd);

            //
            $headers = array(
                __('Total price ABOVE', WOOZASILKOVNASLUG),
                __('Total price UP TO', WOOZASILKOVNASLUG),
                __('Rate ', WOOZASILKOVNASLUG)
            );
            $add_string = __('Add price limit', WOOZASILKOVNASLUG);
            $delete_string = __('Delete', WOOZASILKOVNASLUG);
            $title = __('Set prices based on total price', WOOZASILKOVNASLUG);
            $table_type = 'price';
            $display_type = ($saved_type == $table_type ? 'style="display:table;"' : 'style="display:none"');
            $displayAdd = ($saved_type == $table_type);
            $html .= self::draw_limit_table($zasvl, $table_slug, 'pr', 'pr', $headers, $title, $add_string, $delete_string, $zasilkovna_option, $zasilkovna_prices, $stat, $display, $display_type, $type,$displayAdd);

            $html .= '</td>';
            $html .= '</tr>';

            /**
             *
             */
            $html .= $draw::add_text(
                array(
                    'id' => $slug . '-hmotnost',
                    'placeholder' => '',
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.0001',
                        'min' => '0',
                    ),
                    'label' => __('Maximum weight', WOOZASILKOVNASLUG),
                    'description' => __('Maximum weight allowed by this carrier is ', WOOZASILKOVNASLUG) . $max_weight_info . __('kg', WOOZASILKOVNASLUG),
                    'default' => '',
                    'value' => $zasilkovna_prices[$slug . '-hmotnost'] ?? "",
                )
            );

            $html .= $draw::add_text(
                array(
                    'id' => $slug . '-totalprice',
                    'placeholder' => '',
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.0001',
                        'min' => '0',
                    ),
                    'label' => __('Maximum cart total price', WOOZASILKOVNASLUG),
                    'description' => __('If the total cart value is exceeded, the shipping method will be hidden. Leave blank if not used.', WOOZASILKOVNASLUG),
                    'default' => '',
                    'value' => $zasilkovna_prices[$slug . '-totalprice'] ?? "",
                )
            );

            $html .= $draw::add_checkbox(
                array(
                    'id' => $slug . '-free-coupon',
                    'value' => $zasilkovna_prices[$slug . '-free-coupon'] ?? "",
                    'label' => __('Allow the use of a coupon for free shipping', WOOZASILKOVNASLUG),
                    'description' => '',
                    'cbvalue' => 'ok',
                )
            );

            $html .= $draw::add_checkbox(
                array(
                    'id' => $slug . '-dim-check',
                    'value' => $zasilkovna_prices[$slug . '-dim-check'] ?? "",
                    'label' => __('Enable package dimensions check', WOOZASILKOVNASLUG),
                    'description' => __('If the maximum size is exceeded, the shipping method will not be available at checkout and the package will noft be sent to the Zasilkovna system.', WOOZASILKOVNASLUG),
                    'cbvalue' => 'ok',
                )
            );

            $html .= $draw::add_text(
                array(
                    'id' => $slug . '-dim-one',
                    'placeholder' => '',
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.0001',
                        'min' => '0',
                    ),
                    'label' => __('Maximum size of one side [m]', WOOZASILKOVNASLUG),
                    'description' => __('Maximum size of one side.', WOOZASILKOVNASLUG),
                    'default' => 0.7,
                    'value' => $zasilkovna_prices[$slug . '-dim-one'] ?? '',
                )
            );


            $html .= $draw::add_text(
                array(
                    'id' => $slug . '-dim-sum',
                    'placeholder' => '',
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.0001',
                        'min' => '0',
                    ),
                    'label' => __('Maximum value of the sum of three sides [m]', WOOZASILKOVNASLUG),
                    'description' => __('If the maximum size is exceeded, the shipping method will be unavailable, and the shipment will not be sent to the Zásilkovna system.', WOOZASILKOVNASLUG),
                    'default' => 1.2,
                    'value' => $zasilkovna_prices[$slug . '-dim-sum'] ?? '',
                )
            );

            /*if(strpos($slug, 'box') !== false || strpos($slug,'-pp') !== false) {

                $html .= $draw::add_checkbox(
                    array(
                        'id' => $slug . '-dim-check-box',
                        'value' => $zasilkovna_prices[$slug . '-dim-check-box'] ?? "",
                        'label' => __('Enable package dimensions check for delivery boxes', WOOZASILKOVNASLUG),
                        'description' => __('Checks if items fit within the limits. Dimensions are sorted before comparison (longest vs. longest side, etc.) to account for package rotation.', WOOZASILKOVNASLUG),
                        'cbvalue' => 'ok',
                    )
                );
                $content = sprintf(
                    '<span>' . __('W:', WOOZASILKOVNASLUG) . ' </span><input min="0" type="%s" class="%s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                    esc_attr('number'),
                    esc_attr(''),
                    esc_attr('width:15%'),
                    esc_attr($slug . '-dim-one-w'),
                    esc_attr($slug . '-dim-one-w'),
                    esc_attr($zasilkovna_prices[$slug . '-dim-one-w'] ?? ''),
                    esc_attr(''),
                    '',
                    ''
                );
                $content .= sprintf(
                    '<span>' . __('H:', WOOZASILKOVNASLUG) . ' </span><input min="0" type="%s" class="%s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                    esc_attr('number'),
                    esc_attr(''),
                    esc_attr('width:15%'),
                    esc_attr($slug . '-dim-one-h'),
                    esc_attr($slug . '-dim-one-h'),
                    esc_attr($zasilkovna_prices[$slug . '-dim-one-h'] ?? ''),
                    esc_attr(''),
                    '',
                    ''
                );
                $content .= sprintf(
                    '<span>' . __('L:', WOOZASILKOVNASLUG) . ' </span><input min="0" type="%s" class="%s" style="%s" name="%s" id="%s" value="%s" placeholder="%s" %s %s />',
                    esc_attr('number'),
                    esc_attr(''),
                    esc_attr('width:15%'),
                    esc_attr($slug . '-dim-one-l'),
                    esc_attr($slug . '-dim-one-l'),
                    esc_attr($zasilkovna_prices[$slug . '-dim-one-l'] ?? ''),
                    esc_attr(''),
                    '',
                    ''
                );

                $html .= $draw::add_html($content, ['label' => __('Maximum dimensions [m] ', WOOZASILKOVNASLUG)]);
            }*/

            if ($show_cod_settings) {
                $html .= $draw::add_text(
                    array(
                        'id' => $slug . '-dobirka-max',
                        'placeholder' => '',
                        'type' => 'number',
                        'custom_attributes' => array(
                            'step' => '0.0001',
                            'min' => '0',
                        ),
                        'label' => __('Maximum Cash on Delivery value', WOOZASILKOVNASLUG),
                        'description' => __('Maximum order value up to which the Cash on delivery will be displayed at checkout.', WOOZASILKOVNASLUG),
                        'default' => '',
                        'value' => $zasilkovna_prices[$slug . '-dobirka-max'] ?? "",
                    )
                );
            }

            $html .= $draw::add_text(
                array(
                    'id' => $slug . '-free',
                    'placeholder' => '',
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.0001',
                        'min' => '0',
                    ),
                    'label' => __('Free shipping from', WOOZASILKOVNASLUG),
                    'description' => __('This is the sum of the prices of the products in the basket, tax included.', WOOZASILKOVNASLUG),
                    'default' => '',
                    'value' => $zasilkovna_prices[$slug . '-free'] ?? "",
                )
            );

            /**
             *
             */
            $icon_value = (!empty($zasilkovna_option[$icon_option]) ? $zasilkovna_option[$icon_option] : '');
            $field_args = [
                'id'      => $icon_option,
                'name'    => $icon_option,
                'value'   => $icon_value,
                'label'   => __('Custom icon at checkout', WOOZASILKOVNASLUG),
                'wrapper_class' => 'cell-left',
            ];
            $button_args = [
                'text'  => __('Select icon', WOOZASILKOVNASLUG),
                'class' => 'set_custom_images button toret-secondary',
                'custom_attributes' => [
                    'data-stat' => $stat
                ]
            ];
            $html .= $draw::add_text_with_button($field_args, $button_args);

            if (isset($icon_select_option)) {
                $icon_select_value = (!empty($zasilkovna_option[$icon_select_option]) ? $zasilkovna_option[$icon_select_option] : '');
                $field_args = [
                    'id'      => $icon_select_option,
                    'name'    => $icon_select_option,
                    'value'   => $icon_select_value,
                    'label'   => __('Custom pickup point select icon at checkout', WOOZASILKOVNASLUG),
                    'wrapper_class' => 'cell-left',
                ];
                $button_args = [
                    'text'  => __('Select icon', WOOZASILKOVNASLUG),
                    'class' => 'set_custom_images button toret-secondary',
                    'custom_attributes' => [
                        'data-stat' => $stat
                    ]
                ];
                $html .= $draw::add_text_with_button($field_args, $button_args);
            }

            /**
             *
             */
            if ($show_cod_settings) {
                $html .= '<tr>';
                $html .= '<th scope="row" class="titledesc"><label for="' . $slug . '-fee-type">' . __('Cash on delivery fee calculation type', WOOZASILKOVNASLUG) . '</label></th>';
                $html .= '<td class="forminp forminp-text">';
                $html .= '<select id="' . $slug . '-fee-type" class="select short tzas-fee-type-selection toret-level-selection" data-single="' . $slug . '-dobirka' . '" data-alltables="' . $zasvl . 'pridat-fee-' . $stat . '"  name="' . $slug . '-fee-type">';
                $html .= '<option data-target="' . $zasvl . 'none' . 'pridat-fee-' . $stat . '" value="single"' . ($saved_type_cod == 'single' ? 'selected="selected"' : '') . '>' . __('Flat rate', WOOZASILKOVNASLUG) . '</option>';
                $html .= '<option data-target="' . $zasvl . 'pr' . 'pridat-fee-' . $stat . '" value="total"' . ($saved_type_cod == 'total' ? 'selected="selected"' : '') . '>' . __('Total price-based', WOOZASILKOVNASLUG) . '</option>';
                $html .= '</select>';
                $html .= '</td>';
                $html .= '</tr>';

                //
                $html .= $draw::add_text(
                    array(
                        'id' => $slug . '-dobirka',
                        'placeholder' => '',
                        'type' => 'number',
                        'custom_attributes' => array(
                            'step' => '0.0001',
                            'min' => '0',
                        ),
                        'row_class' => array($slug . '-dobirka'),
                        'label' => __('Cash on delivery fee', WOOZASILKOVNASLUG),
                        'description' => __('Make sure that Cash on delivery payment method is enabled. The flat rate applies when no restrictions are set based on the total price of the order.', WOOZASILKOVNASLUG),
                        'default' => '',
                        'value' => $zasilkovna_prices[$slug . '-dobirka'] ?? "",
                    )
                );

                $option = $slug . '-flr-' . 'cod-enabled';
                $html .= $draw::add_checkbox(
                    array(
                        'id' => $option,
                        'value' =>  $zasilkovna_prices[$option] ?? "",
                        'label' => __('Cash on delivery fee in other currencies', WOOZASILKOVNASLUG),
                        'cbvalue' => 'ok',
                        'class' => 'tzas-flr-cod-checkbox',
                        'custom_attributes' => array(
                            'data-target' => 'tzas' . '-flr-cod-' . $slug,
                        )
                    )
                );

                $currencies = ['CZK','EUR', 'USD', 'GBP', 'PLN', 'HUF', 'RON'];
                $currencies = apply_filters('tzas_foreign_currencies', $currencies);
                foreach ($currencies as $currency) {
                    $option = $slug . '-flr-cod-' . $currency;
                    $html .= $draw::add_text(
                        array(
                            'id' => $option,
                            'placeholder' => '',
                            'label' => __('Cash on delivery fee - ', WOOZASILKOVNASLUG) . $currency,
                            'value' => $zasilkovna_prices[$option] ?? "",
                            'type' => 'number',
                            'custom_attributes' => array(
                                'step' => '0.000001',
                                'min' => '0',
                            ),
                            'wrapper_class' => 'tzas' . '-flr-cod-' . $slug
                        )
                    );
                }

                //
                $html .= '<tr>';
                $html .= '<th></th>';
                $html .= '<td>';
                $html .= '<table ' . $display . ' class="toret-limit-table ' . $zasvl . 'pridat-fee-' . $stat . ' ' . $zasvl . 'pr' . 'pridat-fee-' . $stat . '" id="' . $zasvlfee . 'pridat-' . $stat . '" ' . ($saved_type_cod != 'total' ? 'style="display:none;"' : '') . '>
                                <tr>
                                    <th colspan="4">' . __('Set the Cash on delivery fee based on the price', WOOZASILKOVNASLUG) . '</th>
                                </tr>
                                <tr class="zarovnani-vlevo">
                                    <th>' . __('Price UP TO', WOOZASILKOVNASLUG) . '</th>
                                    <th>' . __('Extra fee', WOOZASILKOVNASLUG) . '</th>
                                    <th></th>
                                </tr>';

                if (!empty($zasilkovna_prices[$slug_cod . '-feeo-' . $stat])) {
                    foreach ($zasilkovna_prices[$slug_cod . '-feeo-' . $stat] as $klic => $hmo) {
                        if (($hmo != '') || ($zasilkovna_prices[$slug_cod . '-feed-' . $stat][$klic] != '') || ($zasilkovna_prices[$slug_cod . '-cenafee-' . $stat][$klic] != '')) {
                            $html .= '<tr id="' . $zasvlfee . $klic . '">';
                            $html .= '<td>';
                            $html .= '<input type="number" min="0" step="0.0001" name="' . $slug_cod . '-feed-' . $stat . '[]" value="' . ($zasilkovna_prices[$slug_cod . '-feed-' . $stat][$klic]  ?? "" != '' ? $zasilkovna_prices[$slug_cod . '-feed-' . $stat][$klic] : '') . '" />';
                            $html .= '</td>';
                            $html .= '<td>';
                            $html .= '<input type="number" min="0" step="0.0001" name="' . $slug_cod . '-cenafee-' . $stat . '[]" value="' . ($zasilkovna_prices[$slug_cod . '-cenafee-' . $stat][$klic] ?? "" != '' ? $zasilkovna_prices[$slug_cod . '-cenafee-' . $stat][$klic] : '') . '" />';
                            $html .= '</td>';
                            $html .= '<td><a href="" class="tzas-feesmazat toret-delete-limit" data-value="' . $zasvlfee . $klic . '">' . __('Delete', WOOZASILKOVNASLUG) . '</a></td>';
                            $html .= '</tr>';
                        }
                    }
                }
                $html .= '</table>';
                $html .= '<input ' . $display . ' data-zasvl="' . $zasvlfee . '" data-slug="' . $slug_cod . '" type="submit" data-native="' . $type . '" class="button tzas-pridatfee ' . $zasvl . 'pridat-fee-' . $stat . ' toret-secondary" data-value="' . $zasvlfee . 'pridat-' . $stat . '" data-stat="' . $stat . '" value="' . __('Add extra fee', WOOZASILKOVNASLUG) . '" ' . ($saved_type_cod != 'total' ? 'style="display:none;"' : '') . '/>';
                $html .= '</td>';
                $html .= '</tr>';
            }

        } else {
            $showMainSaveButton = false;
            $html .= '<tr ' . $display . '><td colspan="2"><p style="text-align: center">' . __('The carrier is not active.', WOOZASILKOVNASLUG) . '</p></td></tr>';
        }

        return self::draw_carrier_settings_end($carrier, $html, $showMainSaveButton);

    }

    static function draw_carrier_settings_end($carrier,$html, $showMainSaveButton): string
    {
        $draw = new Draw();
        $draw->draw_settings_box(
            $html,
            '<span class="dashicons dashicons-edit-page"></span>' .  $carrier['original_title'],
            true,
            [
                'title_tag'   => 'h2',
                'button_text' => __( 'Save', WOOZASILKOVNASLUG ),
            ],
            false,
        );
        return $html;
    }

    static function draw_limit_table($zasvl, $slug, $pricetype, $type, $headers, $title, $add, $delete, $zasilkovna_option, $zasilkovna_prices, $stat, $display, $display_type, $native_type,$displayAdd = false): string
    {

        $html = '<table ' . $display . ' class="toret-limit-table ' . $zasvl . 'pridat-' . $stat . '" id="' . $zasvl . $type . 'pridat-' . $stat . '" ' . $display_type . '>
                                <tr>
                                    <th colspan="4">' . $title . '</th>
                                </tr>
                                <tr class="zarovnani-vlevo">
                                    <th>' . $headers[1] . '</th>
                                    <th>' . $headers[2] . '</th>
                                    <th></th>
                                </tr>';
        if (isset($zasilkovna_prices[$slug . '-' . $type . 'o-' . $stat]) && isset($zasilkovna_prices[$slug . '-' . $type . 'd-' . $stat]) && isset($zasilkovna_prices[$slug . '-cena' . $pricetype . '-' . $stat])) {
            if (!empty($zasilkovna_prices[$slug . '-' . $type . 'o-' . $stat])) {
                foreach ($zasilkovna_prices[$slug . '-' . $type . 'o-' . $stat] as $klic => $hmo) {

                    if (($hmo != '') || ($zasilkovna_prices[$slug . '-' . $type . 'd-' . $stat][$klic] != '') || ($zasilkovna_prices[$slug . '-cena' . $pricetype . '-' . $stat][$klic] != '')) {
                        $html .= '<tr id="' . $zasvl . $type . $klic . '">';
                        $html .= '<td>';
                        $html .= '<input type="number" min="0" step="0.0001" name="' . $slug . '-' . $type . 'd-' . $stat . '[]" value="' . ($zasilkovna_prices[$slug . '-' . $type . 'd-' . $stat][$klic] ?? '') . '" />';
                        $html .= '</td>';
                        $html .= '<td>';
                        $html .= '<input type="number" min="0" step="0.0001" name="' . $slug . '-cena' . $pricetype . '-' . $stat . '[]" value="' . ($zasilkovna_prices[$slug . '-cena' . $pricetype . '-' . $stat][$klic] ?? '') . '" />';
                        $html .= '</td>';
                        $html .= '<td><a href="" class="tzas-' . $type . 'smazat toret-delete-limit" data-value="' . $zasvl . $type . $klic . '">' . $delete . '</a></td>';
                        $html .= '</tr>';
                    }
                }
            }
        }
        $html .= '</table>';
        $html .= '<input ' . $display . ' type="submit" data-zasvl="' . $zasvl . '" data-slug="' . $slug . '" data-type="' . $type . '" data-pricetype="' . $pricetype . '" class="button tzas-pridat' . $type . ' ' . $zasvl . 'pridat-' . $stat . ' toret-secondary" data-value="' . $zasvl . $type . 'pridat-' . $stat . '" data-native="' . $native_type . '"  data-stat="' . $stat . '" value="' . $add . '" style="'.(!$displayAdd ? 'display:none;' : '').'" />';

        return $html;
    }
}

