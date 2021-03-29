<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Makes consistently-formatted requests to the ConvertKit API.
 *
 * @param string $path API path.
 * @param array  $query_args Optional query args.
 * @param null   $request_body Optional request body to be json-encoded and sent with the request.
 * @param array  $request_args Optional API request args.
 *
 * @return array|mixed|object|WP_Error
 */
function ckwc_convertkit_api_request( $path, $query_args = array(), $request_body = null, $request_args = array() ) {
	$path        = ltrim( $path, '/' );
	$request_url = "https://api.convertkit.com/v3/{$path}";

	if ( ! is_null( $request_body ) ) {
		$request_body = wp_json_encode( $request_body );
	}

	$request_args = array_merge(
		array(
			'body'    => $request_body,
			'headers' => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json; charset=utf-8',
			),
			'method'  => 'GET',
			'timeout' => 5,
		),
		$request_args
	);

	if ( ! isset( $query_args['api_key'] ) ) {
		$query_args['api_key'] = ckwc_instance()->api_key;
	}

	$request_url = add_query_arg( $query_args, $request_url );

	$response = wp_remote_request( $request_url, $request_args );

	if ( is_wp_error( $response ) ) {
		return $response;
	} else {
		$response_body = wp_remote_retrieve_body( $response );
		$response_data = json_decode( $response_body, true );

		if ( is_null( $response_data ) ) {
			return new WP_Error( 'parse_failed', __( 'Could not parse response from ConvertKit', 'woocommerce-convertkit' ) );
		} elseif ( isset( $response_data['error'] ) && isset( $response_data['message'] ) ) {
			return new WP_Error( $response_data['error'], $response_data['message'] );
		} else {
			return $response_data;
		}
	}
}

/**
 * Get courses from the specific ConvertKit account.
 *
 * @param string|null $api_key ConvertKit API key.
 *
 * @return array|mixed|object|WP_Error
 */
function ckwc_convertkit_api_get_courses( $api_key = null ) {
	$query_args = is_null( $api_key ) ? array() : array(
		'api_key' => $api_key,
	);

	$response = ckwc_convertkit_api_request( 'courses', $query_args, null );

	return is_wp_error( $response ) ? $response : ( isset( $response['courses'] ) ? array_combine( wp_list_pluck( $response['courses'], 'id' ), $response['courses'] ) : array() );
}

/**
 * Get forms from the specific ConvertKit account.
 *
 * @param string|null $api_key ConvertKit API key.
 *
 * @return array|mixed|object|WP_Error
 */
function ckwc_convertkit_api_get_forms( $api_key = null ) {
	$query_args = is_null( $api_key ) ? array() : array(
		'api_key' => $api_key,
	);

	$response = ckwc_convertkit_api_request( 'forms', $query_args, null );

	return is_wp_error( $response ) ? $response : ( isset( $response['forms'] ) ? array_combine( wp_list_pluck( $response['forms'], 'id' ), $response['forms'] ) : array() );
}

/**
 * Get tags from the specific ConvertKit account.
 *
 * @param string|null $api_key ConvertKit API key.
 *
 * @return array|mixed|object|WP_Error
 */
function ckwc_convertkit_api_get_tags( $api_key = null ) {
	$query_args = is_null( $api_key ) ? array() : array(
		'api_key' => $api_key,
	);

	$response = ckwc_convertkit_api_request( 'tags', $query_args, null );

	return is_wp_error( $response ) ? $response : ( isset( $response['tags'] ) ? array_combine( wp_list_pluck( $response['tags'], 'id' ), $response['tags'] ) : array() );
}

/**
 * Add a subscriber (by name and email) to a specified Course.
 *
 * @param string      $course The course in the ConvertKit account.
 * @param string      $email The subscriber email.
 * @param string      $name The subscriber name.
 * @param string|null $api_key ConvertKit API key.
 *
 * @return array|mixed|object|WP_Error
 */
function ckwc_convertkit_api_add_subscriber_to_course( $course, $email, $name, $api_key = null ) {
	$query_args = is_null( $api_key ) ? array() : array(
		'api_key' => $api_key,
	);

	return ckwc_convertkit_api_request(
		sprintf( 'courses/%d/subscribe', $course ),
		$query_args,
		array(
			'name'  => $name,
			'email' => $email,
		),
		array(
			'method' => 'POST',
		)
	);
}

/**
 * Add a subscriber (by name and email) to a specified Form.
 *
 * @param string      $form The form in the ConvertKit account.
 * @param string      $email The subscriber email.
 * @param string      $name The subscriber name.
 * @param string|null $api_key ConvertKit API key.
 *
 * @return array|mixed|object|WP_Error
 */
function ckwc_convertkit_api_add_subscriber_to_form( $form, $email, $name, $api_key = null ) {
	$query_args = is_null( $api_key ) ? array() : array(
		'api_key' => $api_key,
	);

	return ckwc_convertkit_api_request(
		sprintf( 'forms/%d/subscribe', $form ),
		$query_args,
		array(
			'name'  => $name,
			'email' => $email,
		),
		array(
			'method' => 'POST',
		)
	);
}

/**
 * Add a subscriber (by name and email) to a specified Tag.
 *
 * @param string      $tag The tag in the ConvertKit account.
 * @param string      $email The subscriber email.
 * @param string      $name The subscriber name.
 * @param string|null $api_key ConvertKit API key.
 *
 * @return array|mixed|object|WP_Error
 */
function ckwc_convertkit_api_add_subscriber_to_tag( $tag, $email, $name, $api_key = null ) {
	$query_args = is_null( $api_key ) ? array() : array(
		'api_key' => $api_key,
	);

	return ckwc_convertkit_api_request(
		sprintf( 'tags/%d/subscribe', $tag ),
		$query_args,
		array(
			'name'  => $name,
			'email' => $email,
		),
		array(
			'method' => 'POST',
		)
	);
}
