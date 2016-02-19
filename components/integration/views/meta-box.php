<?php if('yes' !== $this->enabled || empty($this->api_key)) { ?>
<p>
	<strong><?php _e('Important:'); ?></strong>
	<?php _e('The ConvertKit integration for WooCommerce is currently disabled. Customers will not be subscribed when purchasing.'); ?>
</p>
<?php } ?>

<?php if($options) { ?>
<select class="widefat" id="ckwc_subscription" name="ckwc_subscription">
	<option <?php selected('', $subscription); ?> value=""><?php _e('Select a subscription option...'); ?></option>
	<?php foreach($options as $option_group) { if(empty($option_group['options'])) { continue; } ?>
	<optgroup label="<?php echo esc_attr($option_group['name']); ?>">
		<?php foreach($option_group['options'] as $id => $name) { $value = "{$option_group['key']}:{$id}"; ?>
		<option <?php selected($value, $subscription); ?> value="<?php echo esc_attr($value); ?>"><?php echo esc_html($name); ?></option>
		<?php } ?>
	</optgroup>
	<?php } ?>
</select>
<?php } else { ?>
<p>
	<?php _e('Please enter a valid ConvertKit API Key on the ConvertKit Integration Settings page.'); ?>
</p>
<?php } ?>

<?php wp_nonce_field('ckwc', 'ckwc_nonce'); ?>
