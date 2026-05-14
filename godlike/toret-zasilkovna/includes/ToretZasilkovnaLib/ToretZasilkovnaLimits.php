<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ToretZasilkovnaLimits' ) ) {
	class ToretZasilkovnaLimits {
		public function get_country_cod_insurance_limit( $country, $currency, $type ) {
			$matrix = array(
				'CZ' => array(
					'CZK' => array(
						'value' => 20000,
						'cod'   => 20000
					)
				),
				'SK' => array(
					'CZK' => array(
						'value' => 20000,
						'cod'   => 20000
					),
					'EUR' => array(
						'value' => 700,
						'cod'   => 700
					),
				),
				'HU' => array(
					'CZK' => array(
						'value' => 20000,
						'cod'   => 20000
					),
					'HUF' => array(
						'value' => 220000,
						'cod'   =>220000
					),
				),
				'RO' => array(
					'CZK' => array(
						'value' => 20000,
						'cod'   => 20000
					),
					'RON' => array(
						'value' => 3500,
						'cod'   =>3500
					),
				),
				'PL' => array(
					'PLN' => array(
						'value' => 3000,
						'cod'   => 3000
					),
				),
				'DE' => array(
					'EUR' => array(
						'value' => 700,
						'cod'   => 700
					),
				),
				'AT' => array(
					'EUR' => array(
						'value' => 700,
						'cod'   => 700
					),
				),
				'BG' => array(
					'BGN' => array(
						'value' => 1400,
						'cod'   => 1400
					),
				),
				'UA' => array(
					'UAH' => array(
						'value' => 20000,
						'cod'   => 20000
					),
				),
				'GB' => array(
					'GBP' => array(
						'value' => 600,
					),
				),
				'IE' => array(
					'EUR' => array(
						'value' => 700,
					),
				),
				'IT' => array(
					'EUR' => array(
						'value' => 700,
					),
				),
				'FR' => array(
					'EUR' => array(
						'value' => 700,
					),
				),
				'ES' => array(
					'EUR' => array(
						'value' => 700,
						'cod' => 700,
					),
				),
				'PT' => array(
					'EUR' => array(
						'value' => 700,
						'cod' => 700,
					),
				),
				'LI' => array(
					'CHF' => array(
						'value' => 800,
					),
				),
				'CH' => array(
					'CHF' => array(
						'value' => 800,
					),
				),
				'SI' => array(
					'EUR' => array(
						'value' => 700,
						'cod' => 700,
					),
				),
				'HR' => array(
					'HRK' => array(
						'value' => 5200,
						'cod' => 5200,
					),
				),
				'GR' => array(
					'EUR' => array(
						'value' => 700,
						'cod' => 700,
					),
				),
				'DK' => array(
					'DKK' => array(
						'value' => 5200,
					),
				),
				'LV' => array(
					'EUR' => array(
						'value' => 700,
						'cod' => 700,
					),
				),
				'LT' => array(
					'EUR' => array(
						'value' => 700,
						'cod' => 500,
					),
				),
				'EE' => array(
					'EUR' => array(
						'value' => 700,
						'cod' => 700,
					),
				),
				'BE' => array(
					'EUR' => array(
						'value' => 700,
					),
				),
				'NL' => array(
					'EUR' => array(
						'value' => 700,
					),
				),
				'LU' => array(
					'EUR' => array(
						'value' => 700,
					),
				),
				'SE' => array(
					'SEK' => array(
						'value' => 7500,
					),
				),
				'FI' => array(
					'EUR' => array(
						'value' => 700,
					),
				),
				'USA' => array(
					'USD' => array(
						'value' => 800,
					),
				),
				'IL' => array(
					'USD' => array(
						'value' => 700,
					),
				),
				'TR' => array(
					'USD' => array(
						'value' => 700,
					),
				),
				'AE' => array(
					'AED' => array(
						'value' => 5600,
					),
				),

			);
			return $matrix[ $country ][ $currency ][ $type ] ?? self::get_country_cod_insurance_default_limit( $country, $currency, $type );
		}

		private function get_country_cod_insurance_default_limit($country, $currency, $type): int {
			if ( $currency == 'CZK' ) {
				return 20000;
			} else if ( $currency == 'EUR' ) {
				return 700;
			}else
				return 20000;
		}
	}
}