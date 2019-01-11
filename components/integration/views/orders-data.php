<?php

/**
 * Display Orders Data for Sync usage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$show_order_data_btn = false;
if ( null === $unsynced ) {
	$unsynced = __( 'N/A' );
	$show_order_data_btn = true;
} else {
    $unsynced = count( $unsynced );
}

?>
<div class="ckwc-order-data">
	<div class="order-data">
		<svg aria-hidden="true" data-prefix="fas" data-icon="shopping-cart" class="svg-inline--fa fa-shopping-cart fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"></path></svg>
		<strong><?php echo esc_html( $order_count ); ?></strong>
		<p><?php esc_html_e( 'Total Orders' ); ?></p>
	</div>
	<div class="order-data" id="unsyncedOrders">
		<svg aria-hidden="true" data-prefix="fas" data-icon="sync-alt" class="svg-inline--fa fa-sync-alt fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M370.72 133.28C339.458 104.008 298.888 87.962 255.848 88c-77.458.068-144.328 53.178-162.791 126.85-1.344 5.363-6.122 9.15-11.651 9.15H24.103c-7.498 0-13.194-6.807-11.807-14.176C33.933 94.924 134.813 8 256 8c66.448 0 126.791 26.136 171.315 68.685L463.03 40.97C478.149 25.851 504 36.559 504 57.941V192c0 13.255-10.745 24-24 24H345.941c-21.382 0-32.09-25.851-16.971-40.971l41.75-41.749zM32 296h134.059c21.382 0 32.09 25.851 16.971 40.971l-41.75 41.75c31.262 29.273 71.835 45.319 114.876 45.28 77.418-.07 144.315-53.144 162.787-126.849 1.344-5.363 6.122-9.15 11.651-9.15h57.304c7.498 0 13.194 6.807 11.807 14.176C478.067 417.076 377.187 504 256 504c-66.448 0-126.791-26.136-171.315-68.685L48.97 471.03C33.851 486.149 8 475.441 8 454.059V320c0-13.255 10.745-24 24-24z"></path></svg>
		<strong><?php echo esc_html( $unsynced ); ?></strong>
		<p><?php esc_html_e( 'Unsynced Orders' ); ?></p>
	</div>
</div>
<?php

if ( $show_order_data_btn ) {
	?>
	<p>
		<button type="button" id="ckwcOrderData" data-order-count="<?php echo esc_attr( $order_count ); ?>" class="button button-primary"><?php esc_html_e( 'Find Unsynced Orders' ); ?></button>
		<span class="spinner ckwc-spinner"></span>
	</p>
	<?php
}

?>
<p class="<?php if ( $show_order_data_btn ) { echo 'hidden'; } ?> ckwc-sync-unsynced">
    <button type="button" id="ckwcSyncOrders" class="button button-primary"><?php esc_html_e( 'Sync Orders' ); ?></button>
    <span class="spinner ckwc-spinner"></span>
    <span class="hidden ckwc-progress">0%</span>
</p>
