<?php

if(!defined('ABSPATH')) { exit; }

if(!function_exists('ckwc_instance')) {
	function ckwc_instance() {
		$integrations = WC()->integrations->get_integrations();

		return isset($integrations['ckwc']) ? $integrations['ckwc'] : false;
	}
}

if(!function_exists('ckwc_get_subscription_options')) {
	function ckwc_get_subscription_options() {
		$options = get_transient('ckwc_options_' . ckwc_instance()->api_key);

		if(!is_array($options)) {
			$courses = ckwc_convertkit_api_get_courses();
			$forms   = ckwc_convertkit_api_get_forms();
			$tags    = ckwc_convertkit_api_get_tags();

			if(!is_wp_error($courses) && !is_wp_error($forms) && !is_wp_error($tags)) {
				$options = array(
					array(
						'key'     => 'course',
						'name'    => __('Courses'),
						'options' => array_combine(wp_list_pluck($courses, 'id'), wp_list_pluck($courses, 'name')),
					),
					array(
						'key'     => 'form',
						'name'    => __('Forms'),
						'options' => array_combine(wp_list_pluck($forms, 'id'), wp_list_pluck($forms, 'name')),
					),
					array(
						'key'     => 'tag',
						'name'    => __('Tags'),
						'options' => array_combine(wp_list_pluck($tags, 'id'), wp_list_pluck($tags, 'name')),
					),
				);

				set_transient('ckwc_options_' . ckwc_instance()->api_key, $options, 5 * MINUTE_IN_SECONDS);
			}
		}

		return $options;
	}
}

