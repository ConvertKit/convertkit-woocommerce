<?php
/**
 * ConvertKit Admin Coupon class.
 *
 * @package CKWC
 * @author ConvertKit
 */

/**
 * Registers a metabox on WooCommerce Coupons and saves its settings when the
 * Product is saved in the WordPress Administration interface.
 *
 * @package CKWC
 * @author ConvertKit
 */
class CKWC_Admin_Coupon extends CKWC_Admin_Post_Type {

	/**
	 * The Post Type to register the metabox and settings against.
	 *
	 * @since   1.5.9
	 *
	 * @var     string
	 */
	public $post_type = 'shop_coupon';

}
