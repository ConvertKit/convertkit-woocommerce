<h2><?php _e( 'ConvertKit', 'woocommerce-convertkit' ); ?></h2>

<div>
    <p>
    	<?php _e( 'Do not navigate away from this page until this script is done, otherwise complete purchase data will not be sent to ConvertKit. You will be notified via this page when the process is completed.', 'page-generator-pro' ); ?>
    </p>

    <!-- Progress Bar -->
    <div id="progress-bar"></div>
    <div id="progress">
        <span id="progress-number">0</span>
        <span> / <?php echo count( $order_ids ); ?></span>
    </div>

    <!-- Status Updates -->
    <div id="log">
        <ul></ul>
    </div>

    <p>
        <!-- Cancel Button -->
        <a href="#" class="button button-secondary cancel">
            <?php _e( 'Cancel Sync', 'page-generator-pro' ); ?>
        </a>

        <!-- Return Button (display when routine finishes) -->
        <a href="#" class="button button-secondary return">
            <?php _e( 'Return to Settings', 'page-generator-pro' ); ?>
        </a>
    </p>
</div>