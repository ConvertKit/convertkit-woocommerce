<?php
/**
 * Class for Batch Processes.
 */

class CKWC_Batch {

	/**
	 * @var int
	 */
	protected $total = 0;

	/**
	 * @var int
	 */
	protected $step  = 0;

	/**
	 * @var int
	 */
	protected $per_step = 20;

	/**
	 * CKWC_Batch constructor.
	 *
	 * @param integer $step
	 * @param integer $total
	 */
	public function __construct( $step = 1, $total = 0, $per_step = 20 ) {
		$this->total    = $total;
		$this->step     = $step;
		$this->per_step = $per_step;
	}

	/**
	 * Return Total of Items.
	 *
	 * @return int
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Get the offset.
	 *
	 * @return int
	 */
	public function get_offset() {
		return ( $this->step - 1 ) * $this->per_step;
	}

	/**
	 * @return boolean|WP_Error
	 */
	public function process() {

		$items = $this->get_items( $this->get_offset() );

		if ( ! $items ) {
			return new WP_Error( 'no-items', __( 'There were no items to process.' ) );
		}

		foreach ( $items as $item ) {
			$this->process_item( $item );
		}

		return true;
	}

	/**
	 * Get items for the batch process.
	 *
	 * @param $offset
	 *
	 * @return array
	 */
	public function get_items( $offset ) {
		return array();
	}

	/**
	 * Process one Item.
	 *
	 * @param $item
	 */
	public function process_item( $item ) {}

	/**
	 * Get the current progress;
	 *
	 * @return float|int
	 */
	public function get_progress() {
		$total    = $this->get_total();

		if ( 0 === $total ) {
			return 100;
		}

		$progress = $this->step * $this->per_step / $total * 100;

		if ( $progress > 100 ) {
			$progress = 100;
		}

		return $progress;
	}
}