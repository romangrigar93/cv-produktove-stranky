<?php


if ( ! class_exists( 'ToretZasilkovnaDimensionHelper' ) ) {

	class ToretZasilkovnaDimensionHelper {

		/**
		 * Calculate total order weight
		 *
		 * @param $order
		 * @param bool $raw
		 *
		 * @return float|int
		 */
		function get_order_total_weight( $order, bool $raw = false ) {

			$multiplier = self::get_weight_multiplier();

			$total_weight   = 0;
			$product_weight = '';

			foreach ( $order->get_items() as $product_item ) {
				$quantity = $product_item->get_quantity();
				$product  = $product_item->get_product();

				if ( ! empty( $product ) ) {
					if ( $product->is_type( 'variable' ) ) {
						foreach ( $product->get_visible_children() as $variation_id ) {
							$variation = wc_get_product( $variation_id ); // Get the product variation object
							$weight    = $variation->get_weight(); // Get weight from variation

							if ( $weight ) {
								$total_weight += floatval( $weight * $quantity );
							}

						}
					} else {
						$product_weight = $product->get_weight();
					}
				} else {
					$product_weight = '';
				}


				if ( $product_weight != '' ) {
					$total_weight += floatval( $product_weight * $quantity );
				}
			}

			if ( $raw ) {
				return $total_weight;
			} else {
				return $total_weight / $multiplier;
			}

		}

        static function get_zasilkovna_weight($order_id, $zero_allowed = false)
        {
            if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_weight', true)) {
                $weight = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_weight', true);
                $weight = (get_option('woocommerce_weight_unit') == 'g' ? $weight / 1000 : $weight);
            } else if (Toret_HPOS_Compatibility::get_order_meta($order_id, '_cart_weight_kg', true)) {
                $weight = Toret_HPOS_Compatibility::get_order_meta($order_id, '_cart_weight_kg', true);
            } else {
                $weight = Toret_HPOS_Compatibility::get_order_meta($order_id, '_cart_weight', true);
                if ($weight > 0) {
                    $weight = (get_option('woocommerce_weight_unit') == 'g' ? $weight / 1000 : $weight);
                } else {
                    if ($zero_allowed) {
                        $weight = 0;
                    } else {
                        $weight = 0.1;
                    }
                }
            }

            if (empty($weight)) {
                $zasilkovna_option = get_option('zasilkovna_option', array());
                if (!empty($zasilkovna_option['zas_default_weight'])) {
                    $weight = $zasilkovna_option['zas_default_weight'];
                }
            }

            return $weight;
        }

        /**
         * Get weight by weight unit
         */
        public static function get_weight(float $weight): float
        {
            $weight = apply_filters('zasilkovna_weigh_koef', $weight);
            $weight_unit = get_option('woocommerce_weight_unit');
            if (!empty($weight_unit) && $weight_unit == 'g') {
                $weight = $weight * 0.001;
            }

            $zasilkovna_option = get_option('zasilkovna_option');

            if (isset($zasilkovna_option['zas_add_wrap_weight']) && $zasilkovna_option['zas_add_wrap_weight'] > 0) {
                $weight += $zasilkovna_option['zas_add_wrap_weight'];
            }

            return apply_filters('zasilkovna_packeta_weight', $weight);
        }

		function get_weight_multiplier() {

			$multiplier = 1;

			$unit = get_option( 'woocommerce_weight_unit' );

			if ( $unit == 'g' ) {
				$multiplier = 1000;
			} elseif ( $unit == 'lbs' ) {
				$multiplier = 2.205;
			} elseif ( $unit == 'oz' ) {
				$multiplier = 35.2739619;
			}

			return $multiplier;
		}

		/**
		 * Calculate total cart weight
		 *
		 * @return float|int
		 */
		function get_cart_total_weight() {
			$multiplier = self::get_weight_multiplier();
			$weight = WC()->cart->cart_contents_weight;
			return $weight / $multiplier;
		}

		/**
		 * Calculate total order volume
		 *
		 * @param $order
		 * @param bool $raw
		 *
		 * @return float|int
		 */
		function get_order_total_volume( $order, bool $raw = false ) {

			return self::get_total_volume( $order->get_items(), $raw );

		}

		/**
		 * Get cart total volume
		 *
		 * @param $items
		 * @param bool $raw
		 *
		 * @return float|int
		 */
		function get_total_volume( $items, bool $raw = false ) {

			$cart_prods_m3 = array();

			$multiplier = self::get_dim_multiplier( true );

			foreach ( $items as $values ) {
				$_product        = wc_get_product( $values['data']->get_id() );
				$prod_m3         = self::get_product_volume( $_product );
				$cart_prods_m3[] = $prod_m3;
			}

			if ( $raw ) {

				return array_sum( $cart_prods_m3 );

			} else {

				return array_sum( $cart_prods_m3 ) / $multiplier;

			}

		}

		function get_dim_multiplier( $volume = false ) {

			$unit = get_option( 'woocommerce_dimension_unit' );

			$multiplier = 1;

			if ( $volume ) {
				if ( $unit == 'mm' ) {
					$multiplier = 1e9;
				} elseif ( $unit == 'cm' ) {
					$multiplier = 1e6;
				} elseif ( $unit == 'in' ) {
					$multiplier = 61024;
				} elseif ( $unit == 'yd' ) {
					$multiplier = 1.30795;
				}
			} else {
				if ( $unit == 'mm' ) {
					$multiplier = 1000;
				} elseif ( $unit == 'cm' ) {
					$multiplier = 100;
				} elseif ( $unit == 'in' ) {
					$multiplier = 39.3701;
				} elseif ( $unit == 'yd' ) {
					$multiplier = 1.09361;
				}
			}


			return $multiplier;
		}

		/**
		 * Get product volume
		 *
		 * @param $product
		 *
		 * @return float|int
		 */
		function get_product_volume( $product ) {

			return array_product( self::get_product_dimensions( $product ) );

		}

		/**
		 * Get product dimensions
		 *
		 * @param $_product
		 *
		 * @return array|int[]
		 */
		function get_product_dimensions( $_product ): array {

			$l = 0;
			$w = 0;
			$h = 0;

			if ( ! empty( $_product ) ) {
				$l = $_product->get_length();
				$w = $_product->get_width();
				$h = $_product->get_height();
			}
			if ( $l == '' ) {
				$l = 0;
			}
			if ( $w == '' ) {
				$w = 0;
			}
			if ( $h == '' ) {
				$h = 0;
			}

			return array(
				$l,
				$w,
				$h
			);

		}

		/**
		 * Calculate total cart volume
		 *
		 * @param bool $raw
		 *
		 * @return float|int
		 */
		function get_cart_total_volume( bool $raw = false ) {

			return self::get_total_volume( WC()->cart->get_cart(), $raw );

		}

		/**
		 * Get order maximal dimension
		 *
		 * @param $order
		 * @param bool $raw
		 *
		 * @return float|int
		 */
		function get_order_max_dimension( $order, $raw = false ) {

			return self::get_max_dimension( $order->get_items(), $raw );

		}

		/**
		 * Get max dimension from items
		 *
		 * @param $items
		 * @param $raw
		 *
		 * @return float|int|mixed
		 */
		function get_max_dimension( $items, $raw = false, $cart = false ) {

			$cart_dimension = 0;

			$multiplier = self::get_dim_multiplier();

			foreach ( $items as $item ) {

				if ( $cart ) {

					$_product = wc_get_product( $item['data']->get_id() );

				} else {
					$product_id = $item->get_product_id();

					$_product = wc_get_product( $product_id );

					if ( ! empty( $_product ) ) {
						if ( $_product->get_type() == 'variable' ) {
							$product_variation_id = $item->get_variation_id();
							$_product             = wc_get_product( $product_variation_id );
						}
					}
				}

				$product_dimension = self::get_product_max_dimension( $_product );

				if ( $product_dimension > $cart_dimension ) {
					$cart_dimension = $product_dimension;
				}

			}

			if ( $raw ) {
				return $cart_dimension;
			} else {
				return $cart_dimension / $multiplier;
			}

		}

		/**
		 * Get product max dimension
		 *
		 * @param $_product
		 *
		 * @return mixed
		 */
		function get_product_max_dimension( $_product ) {

			return max( self::get_product_dimensions( $_product ) );

		}


		/**
		 * Get order maximum dimensions
		 *
		 * @param $order
		 * @param bool $cart
		 *
		 * @return int[]
		 */
		function get_dimensions( $order, bool $cart = false ): array {

			$cart_dimension = array( 0, 0, 0 );

			$multiplier = self::get_dim_multiplier();

			if ( $cart ) {

				global $woocommerce;
				$items = $woocommerce->cart->get_cart();

				foreach ( $items as $values ) {

					$product = wc_get_product( $values['data']->get_id() );

					$cart_dimension = self::update_max_cart_dimensions( $product, $cart_dimension );

				}

			} else {

				foreach ( $order->get_items() as $item ) {

					$product = $item->get_product();

					$cart_dimension = self::update_max_cart_dimensions( $product, $cart_dimension );

				}
			}

			$cart_dimension[0] = $cart_dimension[0] / $multiplier;
			$cart_dimension[1] = $cart_dimension[1] / $multiplier;
			$cart_dimension[2] = $cart_dimension[2] / $multiplier;

			return $cart_dimension;

		}


		/**
		 * @param $product
		 * @param $cart_dimension
		 *
		 * @return mixed
		 */
		private function update_max_cart_dimensions( $product, $cart_dimension ) {

			$product_dimension = self::get_product_dimensions( $product );

			if ( $product_dimension[0] > $cart_dimension[0] ) {
				$cart_dimension[0] = $product_dimension[0];
			}

			if ( $product_dimension[1] > $cart_dimension[1] ) {
				$cart_dimension[1] = $product_dimension[1];
			}

			if ( $product_dimension[2] > $cart_dimension[2] ) {
				$cart_dimension[2] = $product_dimension[2];
			}

			return $cart_dimension;

		}


		/**
		 * Get cart maximum dimension
		 *
		 * @return float|int
		 */
		function get_cart_max_dimension( $raw = false ) {

			global $woocommerce;

			return self::get_max_dimension( $woocommerce->cart->get_cart(), $raw, true );

		}


		/**
		 * Get cart maximum dimension
		 */
		function get_max_dimension_sum( $cart, $order ) {

			$dimensions = self::get_dimensions( $order, $cart );

			return array_sum( $dimensions );

		}


	}
}