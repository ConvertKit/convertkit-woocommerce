<?php

if ( ! class_exists('CKWC_Batch' ) ) {
	include 'class-ckwc-batch.php';
}

class CKWC_Sync_Orders_Batch extends CKWC_Batch {

	/**
	 * Storing unsynced orders for the current process.
	 * @var null
	 */
	public $unsynced_orders = null;

	/**
	 * A method that will be used for processing each order.
	 *
	 * @var null
	 */
	public $method = null;

	/**
	 * Get items for the batch process.
	 *
	 * @param $offset
	 *
	 * @return array
	 */
	public function get_items( $offset ) {
		$orders = get_option( 'ckwc_unsynched_orders', array() );
		$items  = array();

		$count  = 0;
		foreach ( $orders as $row => $order_id ) {

			if ( $row + 1 < $offset ) {
				continue;
			}

			if ( $count > $this->per_step ) {
				break;
			}

			$items[] = $order_id;

			$count++;
		}

		return $items;
	}

	/**
	 * Process one Item.
	 *
	 * @param $item
	 */
	public function process_item( $item ) {
		$method = $this->method;
		$sent = call_user_func( $method, $item );
		if ( $sent ) {
			$this->unset_unsynced_order( $item );
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
	public function unset_unsynced_order( $order_id ) {
		$unsynced   = $this->get_unsynced_orders();
		$index      = array_search( $order_id, $unsynced );
		if ( false !== $index ) {
			unset( $unsynced[ $index ] );
		}
		$this->unsynced_orders = array_unique( array_values( $unsynced ) );
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