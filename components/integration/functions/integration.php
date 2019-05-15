<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ckwc_instance' ) ) {
	/**
	 * @return bool|CKWC_Integration
	 */
	function ckwc_instance() {
		$integrations = WC()->integrations->get_integrations();

		return isset( $integrations['ckwc'] ) ? $integrations['ckwc'] : false;
	}
}

if ( ! function_exists( 'ckwc_get_subscription_options' ) ) {
	/**
	 * @return array|mixed
	 */
	function ckwc_get_subscription_options() {
		$options = get_transient( 'ckwc_subscription_options' );

		if ( ! is_array( $options ) ) {
			$courses = ckwc_convertkit_api_get_courses();
			$forms   = ckwc_convertkit_api_get_forms();
			$tags    = ckwc_convertkit_api_get_tags();

			/**
			 * Alphabetize
			 */
			usort( $courses, function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			});
			usort( $forms, function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			});
			usort( $tags, function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			});

			if ( ! is_wp_error( $courses ) && ! is_wp_error( $forms ) && ! is_wp_error( $tags ) ) {
				$options = array(
					array(
						'key'     => 'course',
						'name'    => __( 'Courses', 'woocommerce-convertkit' ),
						'options' => array_combine(
							wp_list_pluck( $courses, 'id' ),
							wp_list_pluck( $courses, 'name' )
						),
					),
					array(
						'key'     => 'form',
						'name'    => __( 'Forms', 'woocommerce-convertkit' ),
						'options' => array_combine(
							wp_list_pluck( $forms, 'id' ),
							wp_list_pluck( $forms, 'name' )
						),
					),
					array(
						'key'     => 'tag',
						'name'    => __( 'Tags', 'woocommerce-convertkit' ),
						'options' => array_combine(
							wp_list_pluck( $tags, 'id' ),
							wp_list_pluck( $tags, 'name' )
						),
					),
				);

				set_transient( 'ckwc_subscription_options', $options, 5 * MINUTE_IN_SECONDS );
			}
		}

		return $options;
	}
}
