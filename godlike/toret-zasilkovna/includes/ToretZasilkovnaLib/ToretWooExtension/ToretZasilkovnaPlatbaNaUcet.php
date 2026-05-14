<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
/**
 * @package   Toret Zasilkovna
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      https://toret.cz
 * @copyright 2021 toret.cz
 */


if ( ! function_exists( 'woocommerce_gateway_pnu_init' ) ) {


	add_action( 'plugins_loaded', 'woocommerce_gateway_pnu_init', 0 );

	function woocommerce_gateway_pnu_init() {

        $forceEnable = apply_filters( 'woocommerce_gateway_pnu_force_enable', false );
        $settings = get_option('woocommerce_pnu_settings');
        if( empty($settings) && !$forceEnable){
            return;
        }else{
            $enabled = $settings['enabled'] ?? 'no';
            if( $enabled == 'no' && !$forceEnable){
                return;
            }
        }

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}


		class WC_Gateway_PNU extends WC_Payment_Gateway {

			/**
			 * private variables
			 */
			private $instructions;
			private $enable_for_methods;
			private $enable_pnu_countries;
			private $order_status;
			private $account_details;

			/**
			 * Constructor for the gateway.
			 */
			public function __construct() {

				// Setup general properties
				$this->setup_properties();

				// Load the settings.
				$this->init_form_fields();
				$this->init_settings();

				// Define user set variables
				$this->title                = $this->get_option( 'title' );
				$this->description          = $this->get_option( 'description' );
				$this->instructions         = $this->get_option( 'instructions', $this->description );
				$this->enable_for_methods   = (array) $this->get_option( 'enable_for_methods', array() );
				$this->enable_pnu_countries = (array) $this->get_option( 'enable_pnu_countries', array() );
				$this->order_status         = $this->get_option( 'order_status', array() );

				// pnu account fields shown on the thanks page and in emails
				$this->account_details = get_option( 'woocommerce_pnu_accounts',
					array(
						array(
							'account_name'   => $this->get_option( 'account_name' ),
							'account_number' => $this->get_option( 'account_number' ),
							'sort_code'      => $this->get_option( 'sort_code' ),
							'bank_name'      => $this->get_option( 'bank_name' ),
							'iban'           => $this->get_option( 'iban' ),
							'bic'            => $this->get_option( 'bic' )
						)
					)
				);


				// Actions
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
					$this,
					'process_admin_options'
				) );
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
					$this,
					'save_account_details'
				) );
				add_action( 'woocommerce_thankyou_pnu', array( $this, 'thankyou_page' ) );

				// Customer Emails
				add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
			}

			/**
			 * Setup general properties for the gateway.
			 */
			protected function setup_properties(): void {

				$this->id                 = 'pnu';
				$this->icon               = apply_filters( 'woocommerce_pnu_icon', '' );
				$this->has_fields         = false;
				$this->method_title       = __( 'Bank transfer', WOOZASILKOVNASLUG );
				$this->method_description = __( 'Payments via bank account.', WOOZASILKOVNASLUG );

			}

			/**
			 * Initialise Gateway Settings Form Fields
			 */
			public function init_form_fields(): void {

				if ( function_exists( 'WC' ) ) {
					if ( isset( WC()->countries ) ) {

						$shipping_methods = array();

						if ( is_admin() ) {
							foreach ( WC()->shipping->load_shipping_methods() as $method ) {
								$shipping_methods[ $method->id ] = $method->get_method_title();
							}
						}
						$countries             = WC()->countries->get_allowed_countries();
						$wc_get_order_statuses = wc_get_order_statuses();
						$shop_order_statuses   = $this->alter_wc_statuses( $wc_get_order_statuses );
						$this->form_fields     = array(
							'enabled'              => array(
								'title'   => __( 'Enable / Disable', WOOZASILKOVNASLUG ),
								'type'    => 'checkbox',
								'label'   => __( 'Enable Bank Transfer', WOOZASILKOVNASLUG ),
								'default' => 'no'
							),
							'title'                => array(
								'title'       => __( 'Title', WOOZASILKOVNASLUG ),
								'type'        => 'text',
								'default'     => __( 'Bank transfer', WOOZASILKOVNASLUG ),
								'description' => __( 'Title the customer sees at the checkout.', WOOZASILKOVNASLUG ),
								'desc_tip'    => true,
							),
							'description'          => array(
								'title'       => __( 'Description', WOOZASILKOVNASLUG ),
								'type'        => 'textarea',
								'description' => __( 'Description of the payment method displayed at the checkout.', WOOZASILKOVNASLUG ),
								'default'     => __( 'Make a payment by transferring to a bank account. Use Order ID to identify your payment. Your order will be sent after the payment has been made.', WOOZASILKOVNASLUG ),
								'desc_tip'    => true,
							),
							'instructions'         => array(
								'title'       => __( 'Instructions', WOOZASILKOVNASLUG ),
								'type'        => 'textarea',
								'description' => __( 'The instructions will be displayed on the thank-you page and the checkout.', WOOZASILKOVNASLUG ),
								'default'     => '',
								'desc_tip'    => true,
							),
							'enable_for_methods'   => array(
								'title'       => __( 'Enable delivery method', WOOZASILKOVNASLUG ),
								'type'        => 'multiselect',
								'class'       => 'chosen_select',
								'css'         => 'width: 450px;',
								'default'     => '',
								'description' => __( 'If the cash on delivery is active, you can define the shipping methods here. To enable all modes of transport, leave the field empty.', WOOZASILKOVNASLUG ),
								'options'     => $shipping_methods,
								'desc_tip'    => true,
							),
							'enable_pnu_countries' => array(
								'title'       => __( 'Allow for countries', WOOZASILKOVNASLUG ),
								'type'        => 'multiselect',
								'class'       => 'chosen_select',
								'css'         => 'width: 450px;',
								'default'     => '',
								'description' => __( 'Select countries with available exchange rate.', WOOZASILKOVNASLUG ),
								'options'     => $countries,
								'desc_tip'    => true,
							),
							'order_status'         => array(
								'title'       => __( 'Order Status', WOOZASILKOVNASLUG ),
								'type'        => 'select',
								'class'       => 'chosen_select',
								'css'         => 'width: 450px;',
								'default'     => 'on-hold',
								'description' => __( 'Order status after payment.', WOOZASILKOVNASLUG ),
								'options'     => $shop_order_statuses,
								'desc_tip'    => true,
							),
							'account_details'      => array(
								'type' => 'account_details'
							),
						);
					}
				}
			}

			/**
			 * Alter order statuses
			 */
			public function alter_wc_statuses( array $array ): array {
				$new_array = array();
				foreach ( $array as $key => $value ) {
					$new_array[ substr( $key, 3 ) ] = $value;
				}

				return $new_array;
			}

			/**
			 * generate_account_details_html function.
			 */
			public function generate_account_details_html(): string {
				ob_start();
				?>
                <tr>
                    <th scope="row" class="titledesc"><?php _e( 'Account details', WOOZASILKOVNASLUG ); ?>:</th>
                    <td class="forminp" id="pnu_accounts">
                        <table class="widefat wc_input_table sortable">
                            <thead>
                            <tr>
                                <th class="sort">&nbsp;</th>
                                <th><?php _e( 'Account name', WOOZASILKOVNASLUG ); ?></th>
                                <th><?php _e( 'Account number', WOOZASILKOVNASLUG ); ?></th>
                                <th><?php _e( 'Name of bank', WOOZASILKOVNASLUG ); ?></th>
                                <th><?php _e( 'Sort Code', WOOZASILKOVNASLUG ); ?></th>
                                <th><?php _e( 'IBAN', WOOZASILKOVNASLUG ); ?></th>
                                <th><?php _e( 'BIC / Swift', WOOZASILKOVNASLUG ); ?></th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="7"><a href="#"
                                                   class="add button"><?php _e( '+ Add an account', WOOZASILKOVNASLUG ); ?></a>
                                    <a href="#"
                                       class="remove_rows button"><?php _e( 'Delete Selected Account (Accounts)', WOOZASILKOVNASLUG ); ?></a>
                                </th>
                            </tr>
                            </tfoot>
                            <tbody class="accounts">
							<?php
							$i = - 1;
							if ( $this->account_details ) {
								foreach ( $this->account_details as $account ) {
									$i ++;

									echo '<tr class="account">
		                							<td class="sort"></td>
		                							<td><input type="text" value="' . esc_attr( $account['account_name'] ) . '" name="pnu_account_name[' . $i . ']" /></td>
		                							<td><input type="text" value="' . esc_attr( $account['account_number'] ) . '" name="pnu_account_number[' . $i . ']" /></td>
		                							<td><input type="text" value="' . esc_attr( $account['bank_name'] ) . '" name="pnu_bank_name[' . $i . ']" /></td>
		                							<td><input type="text" value="' . esc_attr( $account['sort_code'] ) . '" name="pnu_sort_code[' . $i . ']" /></td>
		                							<td><input type="text" value="' . esc_attr( $account['iban'] ) . '" name="pnu_iban[' . $i . ']" /></td>
		                							<td><input type="text" value="' . esc_attr( $account['bic'] ) . '" name="pnu_bic[' . $i . ']" /></td>
			                    				</tr>';
								}
							}
							?>
                            </tbody>
                        </table>
                        <script type="text/javascript">
                            jQuery(function () {
                                jQuery('#pnu_accounts').on('click', 'a.add', function () {

                                    var size = jQuery('#pnu_accounts tbody .account').length;

                                    jQuery('<tr class="account">\
		                						<td class="sort"></td>\
		                						<td><input type="text" name="pnu_account_name[' + size + ']" /></td>\
		                						<td><input type="text" name="pnu_account_number[' + size + ']" /></td>\
		                						<td><input type="text" name="pnu_bank_name[' + size + ']" /></td>\
		                						<td><input type="text" name="pnu_sort_code[' + size + ']" /></td>\
		                						<td><input type="text" name="pnu_iban[' + size + ']" /></td>\
		                						<td><input type="text" name="pnu_bic[' + size + ']" /></td>\
			                    			</tr>').appendTo('#pnu_accounts table tbody');

                                    return false;
                                });
                            });
                        </script>
                    </td>
                </tr>
				<?php
				return ob_get_clean();
			}

			/**
			 * Save account details table
			 */
			public function save_account_details(): void {
				$accounts = array();

				if ( isset( $_POST['pnu_account_name'] ) ) {

					$account_names   = array_map( 'wc_clean', $_POST['pnu_account_name'] );
					$account_numbers = array_map( 'wc_clean', $_POST['pnu_account_number'] );
					$bank_names      = array_map( 'wc_clean', $_POST['pnu_bank_name'] );
					$sort_codes      = array_map( 'wc_clean', $_POST['pnu_sort_code'] );
					$ibans           = array_map( 'wc_clean', $_POST['pnu_iban'] );
					$bics            = array_map( 'wc_clean', $_POST['pnu_bic'] );

					foreach ( $account_names as $i => $name ) {
						if ( ! isset( $name ) ) {
							continue;
						}

						$accounts[] = array(
							'account_name'   => $name,
							'account_number' => $account_numbers[ $i ],
							'bank_name'      => $bank_names[ $i ],
							'sort_code'      => $sort_codes[ $i ],
							'iban'           => $ibans[ $i ],
							'bic'            => $bics[ $i ]
						);
					}
				}

				update_option( 'woocommerce_pnu_accounts', $accounts );
			}

			/**
			 * Check if is virtual product in cart
			 */
			public function is_virtual_product_in_cart(): bool {

				if ( is_admin() ) {
					return false;
				}

				$has_virtual = true;
				$cart_data   = array();
				if ( WC()->session ) {
					if ( WC()->session->cart ) {
						if ( ! empty( WC()->session->cart->cart_contents ) ) {
							$cart_data = WC()->session->cart->cart_contents;
						} else {
							$cart_data = WC()->session->cart;
						}
					}
				}
				if ( ! empty( $cart_data ) ) {
					foreach ( $cart_data as $item ) {

						$product = wc_get_product( $item['variation_id'] ?: $item['product_id'] );
						if ( ! $product->is_virtual() ) {
							$has_virtual = false;
						}
					}
				}

				return $has_virtual;

			}

			/**
			 * Check If The Gateway Is Available For Use
			 */
			function is_available(): bool {

				if ( is_admin() ) {
					return parent::is_available();
				}

				if ( $this->is_virtual_product_in_cart() ) {
					return parent::is_available();
				}

				$enable_for_country = $this->is_available_for_country();
				if ( $enable_for_country === false ) {
					return false;
				}

				if ( ! empty( $this->enable_for_methods ) ) {

					// Only apply if all packages are being shipped via local pickup
					$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

					if ( isset( $chosen_shipping_methods_session ) ) {
						$chosen_shipping_methods = array_unique( $chosen_shipping_methods_session );
					} else {
						$chosen_shipping_methods = array();
					}

					$check_method = false;

					if ( is_page( wc_get_page_id( 'checkout' ) ) && ! empty( $wp->query_vars['order-pay'] ) ) {

						$order_id = absint( $wp->query_vars['order-pay'] );
						$order    = wc_get_order( $order_id );

						if ( $order->shipping_method ) {
							$check_method = $order->shipping_method;
						}

					} elseif ( empty( $chosen_shipping_methods ) || sizeof( $chosen_shipping_methods ) > 1 ) {
						$check_method = false;
					} elseif ( sizeof( $chosen_shipping_methods ) == 1 ) {
						$check_method = $chosen_shipping_methods[0];
					}

					if ( ! $check_method ) {
						return false;
					}

					$found = false;

					foreach ( $this->enable_for_methods as $method_id ) {
						if ( strpos( $check_method, $method_id ) === 0 ) {
							$found = true;
							break;
						}
					}

					if ( ! $found ) {
						return false;
					}
				}

				//User role shipping fix
				if ( isset( WC()->session ) ) {
					$session = WC()->session;
					if ( isset( $session->chosen_shipping_methods ) ) {
						$chosen_shipping_methods = $session->chosen_shipping_methods;
						if ( isset( $chosen_shipping_methods[0] ) && ! empty( $chosen_shipping_methods[0] ) ) {
							if ( $chosen_shipping_methods[0] == 'user-role>dobirka-pro-partnery' ) {
								return false;
							}
						}
					}
				}

				return parent::is_available();
			}


			/**
			 * Check is payment method available for selected country
			 */
			public function is_available_for_country(): bool {

				if ( ! empty( WC()->customer ) ) {

					$country = $this->get_customer_country();

					if ( ! empty( $this->enable_pnu_countries ) ) {

						if ( ! in_array( $country, $this->enable_pnu_countries ) ) {
							$return = false;
						} else {
							$return = true;
						}

					} else {
						$return = true;
					}
				} else {
					$return = false;
				}

				return $return;
			}

			/**
			 * Output for the order received page.
			 */
			public function thankyou_page( int $order_id ): void {
				if ( $this->instructions ) {
					echo wpautop( wptexturize( wp_kses_post( $this->instructions ) ) );
				}
				$this->bank_details( $order_id );
			}

			/**
			 * Add content to the WC emails
			 */
			public function email_instructions( WC_Order $order, bool $sent_to_admin ): void {

				if ( $sent_to_admin || $order->get_status() !== 'on-hold' || $order->get_payment_method() !== 'pnu' ) {
					return;
				}

				if ( $this->instructions ) {
					echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
				}

				$this->bank_details( $order->get_id() );
			}

			/**
			 * Get bank details and place into a list format
			 */
			private function bank_details( int $order_id = 0 ): void {
				if ( empty( $this->account_details ) ) {
					return;
				}

				echo '<h2>' . __( 'Bank account detail', WOOZASILKOVNASLUG ) . '</h2>' . PHP_EOL;

				$pnu_accounts = apply_filters( 'woocommerce_pnu_accounts', $this->account_details );

				if ( ! empty( $pnu_accounts ) ) {
					foreach ( $pnu_accounts as $pnu_account ) {
						echo '<ul class="order_details pnu_details">' . PHP_EOL;

						$pnu_account = (object) $pnu_account;

						// pnu account fields shown on the thanks page and in emails
						$account_fields = apply_filters( 'woocommerce_pnu_account_fields', array(
							'account_number' => array(
								'label' => __( 'Account number', WOOZASILKOVNASLUG ),
								'value' => $pnu_account->account_number
							),
							'sort_code'      => array(
								'label' => __( 'Sort Code', WOOZASILKOVNASLUG ),
								'value' => $pnu_account->sort_code
							),
							'iban'           => array(
								'label' => __( 'IBAN', WOOZASILKOVNASLUG ),
								'value' => $pnu_account->iban
							),
							'bic'            => array(
								'label' => __( 'BIC', WOOZASILKOVNASLUG ),
								'value' => $pnu_account->bic
							)
						), $order_id );

						if ( $pnu_account->account_name || $pnu_account->bank_name ) {
							echo '<h3>' . implode( ' - ', array_filter( array(
									$pnu_account->account_name,
									$pnu_account->bank_name
								) ) ) . '</h3>' . PHP_EOL;
						}

						foreach ( $account_fields as $field_key => $field ) {
							if ( ! empty( $field['value'] ) ) {
								echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li>' . PHP_EOL;
							}
						}

						echo '</ul>';
					}
				}
			}

			/**
			 * Process the payment and return the result
			 */
			public function process_payment( $order_id ): array {

				$order = wc_get_order( $order_id );

				// Mark as on-hold (we're awaiting the cheque)
				if ( ! empty( $this->order_status ) ) {
					$order->update_status( $this->order_status, __( 'Awaiting completion of bank transfer', WOOZASILKOVNASLUG ) );
				} else {
					$order->update_status( 'on-hold', __( 'Awaiting completion of bank transfer', WOOZASILKOVNASLUG ) );
				}

				// Reduce stock levels
				wc_reduce_stock_levels( $order_id );

				// Remove cart
				WC()->cart->empty_cart();

				// Return thankyou redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			}

			/**
			 *  Add the Gateway to WooCommerce
			 */
			public function get_customer_country(): string {
				$shipping_country = WC()->customer->get_shipping_country();
				if ( ! empty( $shipping_country ) ) {
					$country = WC()->customer->get_shipping_country();
				} else {
					$country = WC()->customer->get_billing_country();
				}
				return $country;
			}

		}
	}//End class

	/**
	 *
	 *  Add the Gateway to WooCommerce
	 */
	function woocommerce_add_gateway_pnu( $methods ) {
		$methods[] = 'WC_Gateway_PNU';

		return $methods;
	}

	//Woocommerce payment gateways filter
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_pnu' );

}