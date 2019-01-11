<?php

if ( ! class_exists('CKWC_Batch' ) ) {
	include 'class-ckwc-batch.php';
}

class CKWC_Unsynced_Batch extends CKWC_Batch {

	/**
	 * Storing unsynced orders for the current process.
	 * @var null
	 */
	public $unsynced_orders = null;

	/**
	 * Get items for the batch process.
	 *
	 * @param $offset
	 *
	 * @return array
	 */
	public function get_items( $offset ) {
		return wc_get_orders( array( 'offset' => $offset, 'limit' => $this->per_step ) );
	}

	/**
	 * Process one Item.
	 *
	 * @param $item
	 */
	public function process_item( $item ) {
		if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
			$order_id = $item->id;
		} else {
			$order_id = $item->get_id();
		}

		$purchase_id = get_post_meta( $order_id, '_ck_purchase_id', true );

		if ( ! $purchase_id ) {
			$this->set_unsynced_order( $order_id );
		}
	}

	/**
	 * Get Unsynced Orders.
	 *
	 * @return array
	 */
	public function get_unsynced_orders() {
		if ( null === $this->unsynced_orders ) {
			$this->unsynced_orders = get_option( 'ckwc_unsynched_orders', array() );
		}

		return $this->unsynced_orders;
	}

	/**
	 * Get Unsynced Orders.
	 *
	 * @return array
	 */
	public function set_unsynced_order( $order_id ) {
		$unsynced = $this->get_unsynced_orders();
		$unsynced[] = $order_id;
		$this->unsynced_orders = array_unique( $unsynced );
	}

	/**
	 * Get total. If zero, we'll try to find the total.
	 */
	public function get_total() {
		if ( 0 === $this->total ) {
			$this->total = $this->get_total_count();
		}

		return $this->total;
	}

	/**
	 * Return the count of total orders.
	 *
	 * @return int
	 */
	private function get_total_count() {
		// No WHERE clause for a faster retrieveal.
		$system_status    = new WC_REST_System_Status_Controller();
		$post_type_counts = $system_status->get_post_type_counts();
		$post_type_counts = array_values( array_filter( $post_type_counts, array( $this, 'only_order_type' ) ) );
		return $post_type_counts ? $post_type_counts[0]->count : 0;
	}

	/**
	 * Return an array element if it's an order type.
	 *
	 * @see CKWC_Integration::get_order_count
	 *
	 * @param $object
	 *
	 * @return bool
	 */
	public function only_order_type( $object ) {
		if ( 'shop_order' !== $object->type ) { return false; }
		return true;
	}
}