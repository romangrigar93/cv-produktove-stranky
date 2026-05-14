<?php


if ( ! class_exists( 'ToretZasilkovnaCustoms' ) ) {

	class ToretZasilkovnaCustoms {

		function is_declaration_enabled( $shipping_method, $country ) {

			if ( in_array($country,TORET_ZASILKOVNA_ENABLE_CUSTOMS_COUNTRIES) == 'GB' && TORET_ZASILKOVNA_ENABLE_CUSTOMS && ( in_array( $shipping_method, TORET_ZASILKOVNA_ENABLE_CUSTOMS_CARRIERS ) || empty( $shipping_method ) ) ) {
				return true;
			} else {
				return false;
			}
		}

		function is_custom_codes_available( $order_id ) {

			$order = wc_get_order( $order_id );

			$codes = [];

			foreach ( $order->get_items() as $item_id => $item ) {
				$product_id = $item->get_product_id();

				$custom_code = get_post_meta( $product_id, '_zasilkovna_custom_code', true );
				if ( $custom_code == '' ) {
					$codes[ $product_id ] = $item->get_name();
				}
			}

			return $codes;

		}

		function is_weights_available( $order_id ) {

			$order = wc_get_order( $order_id );

			$weights = [];

			foreach ( $order->get_items() as $item_id => $item ) {

				if ( $item->get_variation_id() > 0 ) {
					$id = $item->get_variation_id();
				} else {
					$id = $item->get_product_id();
				}

				$product = wc_get_product( $id );

				$weight = $product->get_weight();

				if ( $weight == '' || $weight == 0 ) {
					$weights[ $id ] = $product->get_name();
				}
			}

			return $weights;

		}


		function is_en_titles_available( $order_id ) {

			$order = wc_get_order( $order_id );

			$zasilkovna_option = get_option( 'zasilkovna_option' );
			$orig_titles       = $zasilkovna_option['customs_en_as_orig'] ?? '';

			$any_empty = false;
			$titles    = [];

			foreach ( $order->get_items() as $item_id => $item ) {

				$product_id = $item->get_product_id();

				$title = get_post_meta( $product_id, '_zasilkovna_en_product_title', true );

				if ( $title == '' && $orig_titles != 'ok' ) {
					$titles[ $product_id ] = $item->get_name();
				}
			}

			return $titles;
		}


		function is_invoice_number_available( $order_id ) {

			$zasilkovna_option = get_option( 'zasilkovna_option' );
			$source            = $zasilkovna_option['invoice_number_source'] ?? '';
			$meta              = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'tzas-invoice-number', true );

			if ( $meta != '' ) {
				return $meta;
			} elseif ( $source == 'orderid' ) {
				return $order_id;
			} elseif ( $source == 'ordernumber' ) {
				$order = wc_get_order( $order_id );

				return $order->get_order_number();
			} elseif ( $source == 'tidoklad' ) {
				$number = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'idoklad_invoice_id', true );

				return ( $number != '' ? $number : false );
			} elseif ( $source == 'tfakturoid' ) {
				$number = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'fakturoid_invoice_id', true );

				return ( $number != '' ? $number : false );
			} elseif ( $source == 'tvyfakturuj' ) {
				$number = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'vyfakturuj_invoice_id', true );

				return ( $number != '' ? $number : false );
			} else {
				return false;
			}

		}


		function is_invoice_date_available( $order_id ) {

			$zasilkovna_option = get_option( 'zasilkovna_option' );
			$source            = $zasilkovna_option['invoice_date_source'] ?? '';
			$meta              = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'tzas-invoice-date', true );

			if ( $meta != '' ) {
				return $meta;
			} elseif ( $source == 'orderpaid' ) {
				$order = wc_get_order( $order_id );
				$date  = $order->get_date_paid();
				if ( ! empty( $date ) ) {
					$date->date( "Y-m-d" );

					return $date;
				} else {
					return false;
				}
			} elseif ( $source == 'tidoklad' ) {
				$number = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'idoklad_invoice_issued_date', true );

				return ( $number != '' ? $number : false );
			} elseif ( $source == 'tfakturoid' ) {
				$number = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'fakturoid_invoice_date', true );

				return ( $number != '' ? $number : false );
			} elseif ( $source == 'tvyfakturuj' ) {
				$number = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'vyfakturuj_invoice_date', true );

				return ( $number != '' ? $number : false );
			} else {
				return false;
			}
		}

		function is_declaration_ok( $order_id )
        {
			$declaration_ok = true;

			$order = wc_get_order( $order_id );

			if ( empty( $id_zasilky ) && self::is_declaration_enabled( null, $order->get_shipping_country() ) ) {

				$invoice_number = self::is_invoice_number_available( $order_id );
				$invoice_date   = self::is_invoice_date_available( $order_id );
				$customs_codes  = self::is_custom_codes_available( $order_id );
				$en_titles      = self::is_en_titles_available( $order_id );
				$weights        = self::is_weights_available( $order_id );

				if ( ! $invoice_number ) {
					$declaration_ok = false;
				}

				if ( ! $invoice_date ) {
					$declaration_ok = false;
				}

				if ( count( $customs_codes ) > 0 ) {
					$declaration_ok = false;
				}

				if ( count( $en_titles ) > 0 ) {
					$declaration_ok = false;
				}

				if ( count( $weights ) > 0 ) {
					$declaration_ok = false;
				}

			}

			return $declaration_ok;
		}


		function admin_popup_dialog( $post_id, $metabox = false ) {

			$popup = '<a class="button toret-customs-info toret-customs-info' . $post_id . ' '.($metabox ? 'toret-customs-info-metabox' : '') .'" data-id="' . $post_id . '" style="padding: 2px 4px 1px 5px;"><span class="dashicons dashicons-warning" title="' . __( 'Customs declaration', 'zasilkovna' ) . '"></span>' . ( $metabox ? __( 'Check issues', WOOZASILKOVNASLUG ) : '' ) . '</a>';

			$popup .= '<div class="toret-customs-info-' . $post_id . ' toret-popup toret-popup-add-info" style="display:none;">';
			$popup .= '<div class="toret-popup-inner">';
			$popup .= '<h2 class="zasilkovna-customs-title" >' . __( 'Customs declaration', 'zasilkovna' ) . '</h2>';

			$invoice_number = self::is_invoice_number_available( $post_id );
			$invoice_date   = self::is_invoice_date_available( $post_id );
			$customs_codes  = self::is_custom_codes_available( $post_id );
			$en_titles      = self::is_en_titles_available( $post_id );
			$weights        = self::is_weights_available( $post_id );

			$showSave = false;

			$popup .= '<div class="toret-popup-inner-customs ' . ( $metabox ? "toret-popup-inner-customs-metabox" : "" ) . '">';

			if ( ! $invoice_number ) {
				$popup .= '<p class="zasilkovna-customs-group"><span class="zasilkovna-customs-warning">' . __( 'Invoice number is required!', WOOZASILKOVNASLUG ) . '</span>';
				if ( ! $metabox ) {
					$popup    .= '<input type="text" value="' . $invoice_number . '" id="tzas-invoice-number" class="toret-input-invoice-number toret-input-invoice-number' . $post_id . '" />';
					$showSave = true;
				}
				$popup .= '</p>';
			}

			if ( ! $invoice_date ) {
				$popup .= '<p class="zasilkovna-customs-group"><span class="zasilkovna-customs-warning">' . __( 'Invoice issue date is required!', WOOZASILKOVNASLUG ) . '</span>';
				if ( ! $metabox ) {
					$popup    .= '<input type="text" value="' . $invoice_date . '" id="tzas-invoice-date" class="toret-input-invoice-date toret-input-invoice-date' . $post_id . '" />';
					$showSave = true;
				}
				$popup .= '</p>';
			}

			if ( count( $customs_codes ) > 0 ) {
				$popup .= '<p class="zasilkovna-customs-group"><span class="zasilkovna-customs-warning">' . __( 'Some order items miss customs code!', WOOZASILKOVNASLUG ) . '</span>';
				foreach ( $customs_codes as $id => $customs_code ) {
					$popup .= '<a href="' . get_edit_post_link( $id ) . '" target="_blank"> - ' . $customs_code . '</a>';
				}
				$popup .= '</p>';
			}

			if ( count( $en_titles ) > 0 ) {
				$popup .= '<p class="zasilkovna-customs-group"><span class="zasilkovna-customs-warning">' . __( 'Some order items miss product title in EN!', WOOZASILKOVNASLUG ) . '</span>';
				foreach ( $en_titles as $id => $en_title ) {
					$popup .= '<a href="' . get_edit_post_link( $id ) . '" target="_blank"> - ' . $en_title . '</a>';
				}
				$popup .= '</p>';
			}


			if ( count( $weights ) > 0 ) {
				$popup .= '<p class="zasilkovna-customs-group"><span class="zasilkovna-customs-warning">' . __( 'Some order items miss weight property!', WOOZASILKOVNASLUG ) . '</span>';
				foreach ( $weights as $id => $weight ) {
					$popup .= '<a href="' . get_edit_post_link( $id ) . '" target="_blank"> - ' . $weight . '</a>';
				}
				$popup .= '</p>';
			}


			$popup .= '</div>';
			$popup .= '<div class="zasilkovna-customs-buttons">';

			if ( $showSave ) {
				$popup .= '<button class="tzas-ulozit toret-customs-popup-save" data-id="' . $post_id . '">' . __( 'Save', 'zasilkovna' ) . '</button>';
			}
			$popup .= '<button style="margin-left: 5px;" class="tzas-ulozit toret-popup-close" data-id="' . $post_id . '">' . __( 'Close', 'zasilkovna' ) . '</button>';


			$popup .= '</div>';
			$popup .= '</div>';
			$popup .= '</div>';

			return $popup;

		}


		public function create_storage_file( $order_id, $type, $fileid ) {

			$zasilkovna_option = get_option( 'zasilkovna_option' );

			$file_path = wp_get_original_image_path( $fileid );
			if ( $file_path == '' ) {
				$file_path = get_attached_file( $fileid );
			}

			if ( $file_path != '' ) {

				$filename = explode( '/', $file_path );

				if ( ! empty( $zasilkovna_option['api_password'] ) ) {

					$apiPassword = $zasilkovna_option['api_password'];
					$gw          = new SoapClient( "https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl" );

					try {

						$storageFile = $gw->createStorageFile( $apiPassword, [
							'content' => base64_encode( file_get_contents( $file_path ) ),
							'name'    => end( $filename ),
						] );

						if ( isset( $storageFile->id ) ) {
							Toret_HPOS_Compatibility::update_order_meta( $order_id, 'zasilkovna_' . $type . '_file_id', $storageFile->id );
						}

					} catch ( SoapFault $e ) {

						//tzas_var_error_log( $e->detail ); // property detail contains error info

					}

				}
			}
		}

		public function get_order_dopravce($order_id){
			$zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta( $order_id, 'zasilkovna_id_dopravy');
            return tzas_get_service_from_string($zasilkovna_shipping);
		}

	}

}