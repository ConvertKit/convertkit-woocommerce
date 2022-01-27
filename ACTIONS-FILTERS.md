<h1>Filters</h1><table>
				<thead>
					<tr>
						<th>File</th>
						<th>Filter Name</th>
						<th>Description</th>
					</tr>
				</thead>
				<tbody><tr>
						<td colspan="3">../includes/class-ckwc-checkout.php</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_checkout_add_opt_in_checkbox"><code>convertkit_for_woocommerce_checkout_add_opt_in_checkbox</code></a></td>
						<td>Adds the opt-in checkbox to the checkout's billing or order section, based on the Plugin's settings.</td>
					</tr><tr>
						<td colspan="3">../includes/class-ckwc-api.php</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_api_get_timeout"><code>convertkit_api_get_timeout</code></a></td>
						<td>Defines the maximum time to allow the API request to run.</td>
					</tr><tr>
						<td colspan="3">../includes/class-ckwc-order.php</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_order_maybe_subscribe_customer_resource_id"><code>convertkit_for_woocommerce_order_maybe_subscribe_customer_resource_id</code></a></td>
						<td>Define the Form, Tag or Sequence ID to subscribe the Customer to for the given Product.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_order_maybe_subscribe_customer_subscriptions"><code>convertkit_for_woocommerce_order_maybe_subscribe_customer_subscriptions</code></a></td>
						<td>Define the Forms, Tags and/or Sequences to subscribe the Customer to for this Order.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_order_send_purchase_data"><code>convertkit_for_woocommerce_order_send_purchase_data</code></a></td>
						<td>Define the data to send to the ConvertKit API to create a Purchase in ConvertKit https://developers.convertkit.com/#create-a-purchase</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_email"><code>convertkit_for_woocommerce_email</code></a></td>
						<td>Returns the customer's email address for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form or Tag.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_order_name"><code>convertkit_for_woocommerce_order_name</code></a></td>
						<td>Returns the customer's name for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form, Tag or Sequence.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_first_name"><code>convertkit_for_woocommerce_first_name</code></a></td>
						<td>Returns the customer's first name for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form, Tag or Sequence.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_last_name"><code>convertkit_for_woocommerce_last_name</code></a></td>
						<td>Returns the customer's last name for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form or Tag.</td>
					</tr>
					</tbody>
				</table><h3 id="convertkit_for_woocommerce_checkout_add_opt_in_checkbox">
						convertkit_for_woocommerce_checkout_add_opt_in_checkbox
						<code>includes/class-ckwc-checkout.php::78</code>
					</h3><h4>Overview</h4>
						<p>Adds the opt-in checkbox to the checkout's billing or order section, based on the Plugin's settings.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>Checkout</td>
							<td>array $fields</td>
							<td>Fields.</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_checkout_add_opt_in_checkbox', function( $fields ) {
	// ... your code here
	// Return value
	return $fields;
}, 10, 1 );
</pre>
<h3 id="convertkit_api_get_timeout">
						convertkit_api_get_timeout
						<code>includes/class-ckwc-api.php::1157</code>
					</h3><h4>Overview</h4>
						<p>Defines the maximum time to allow the API request to run.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$timeout</td>
							<td>int</td>
							<td>Timeout, in seconds.</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_api_get_timeout', function( $timeout ) {
	// ... your code here
	// Return value
	return $timeout;
}, 10, 1 );
</pre>
<h3 id="convertkit_for_woocommerce_order_maybe_subscribe_customer_resource_id">
						convertkit_for_woocommerce_order_maybe_subscribe_customer_resource_id
						<code>includes/class-ckwc-order.php::116</code>
					</h3><h4>Overview</h4>
						<p>Define the Form, Tag or Sequence ID to subscribe the Customer to for the given Product.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$resource_id</td>
							<td>mixed</td>
							<td>Form, Tag or Sequence ID | empty string.</td>
						</tr><tr>
							<td>$order_id</td>
							<td>int</td>
							<td>WooCommerce Order ID.</td>
						</tr><tr>
							<td>Order's</td>
							<td>string $status_new</td>
							<td>New</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_order_maybe_subscribe_customer_resource_id', function( $resource_id, $order_id, $status_old, $status_new ) {
	// ... your code here
	// Return value
	return $resource_id;
}, 10, 4 );
</pre>
<h3 id="convertkit_for_woocommerce_order_maybe_subscribe_customer_subscriptions">
						convertkit_for_woocommerce_order_maybe_subscribe_customer_subscriptions
						<code>includes/class-ckwc-order.php::140</code>
					</h3><h4>Overview</h4>
						<p>Define the Forms, Tags and/or Sequences to subscribe the Customer to for this Order.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$subscriptions</td>
							<td>array</td>
							<td>Subscriptions (array of Forms, Tags and/or Sequence IDs).</td>
						</tr><tr>
							<td>$order_id</td>
							<td>int</td>
							<td>WooCommerce Order ID.</td>
						</tr><tr>
							<td>Order's</td>
							<td>string $status_new</td>
							<td>New</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_order_maybe_subscribe_customer_subscriptions', function( $subscriptions, $order_id, $status_old, $status_new ) {
	// ... your code here
	// Return value
	return $subscriptions;
}, 10, 4 );
</pre>
<h3 id="convertkit_for_woocommerce_order_send_purchase_data">
						convertkit_for_woocommerce_order_send_purchase_data
						<code>includes/class-ckwc-order.php::342</code>
					</h3><h4>Overview</h4>
						<p>Define the data to send to the ConvertKit API to create a Purchase in ConvertKit https://developers.convertkit.com/#create-a-purchase</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$purchase</td>
							<td>array</td>
							<td>Purchase Data.</td>
						</tr><tr>
							<td>$order_id</td>
							<td>int</td>
							<td>WooCommerce Order ID.</td>
						</tr><tr>
							<td>Order's</td>
							<td>string $status_new</td>
							<td>New</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_order_send_purchase_data', function( $purchase, $order_id, $status_old, $status_new ) {
	// ... your code here
	// Return value
	return $purchase;
}, 10, 4 );
</pre>
<h3 id="convertkit_for_woocommerce_email">
						convertkit_for_woocommerce_email
						<code>includes/class-ckwc-order.php::478</code>
					</h3><h4>Overview</h4>
						<p>Returns the customer's email address for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form or Tag.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$email</td>
							<td>string</td>
							<td>Email Address</td>
						</tr><tr>
							<td>$order</td>
							<td>WC_Order|WC_Order_Refund</td>
							<td>Order</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_email', function( $email, $order ) {
	// ... your code here
	// Return value
	return $email;
}, 10, 2 );
</pre>
<h3 id="convertkit_for_woocommerce_order_name">
						convertkit_for_woocommerce_order_name
						<code>includes/class-ckwc-order.php::533</code>
					</h3><h4>Overview</h4>
						<p>Returns the customer's name for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form, Tag or Sequence.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$name</td>
							<td>string</td>
							<td>Name</td>
						</tr><tr>
							<td>$order</td>
							<td>WC_Order|WC_Order_Refund</td>
							<td>Order</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_order_name', function( $name, $order ) {
	// ... your code here
	// Return value
	return $name;
}, 10, 2 );
</pre>
<h3 id="convertkit_for_woocommerce_first_name">
						convertkit_for_woocommerce_first_name
						<code>includes/class-ckwc-order.php::565</code>
					</h3><h4>Overview</h4>
						<p>Returns the customer's first name for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form, Tag or Sequence.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$first_name</td>
							<td>string</td>
							<td>First Name</td>
						</tr><tr>
							<td>$order</td>
							<td>WC_Order|WC_Order_Refund</td>
							<td>Order</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_first_name', function( $first_name, $order ) {
	// ... your code here
	// Return value
	return $first_name;
}, 10, 2 );
</pre>
<h3 id="convertkit_for_woocommerce_last_name">
						convertkit_for_woocommerce_last_name
						<code>includes/class-ckwc-order.php::597</code>
					</h3><h4>Overview</h4>
						<p>Returns the customer's last name for the given WooCommerce Order, immediately before it is sent to ConvertKit when subscribing the Customer to a Form or Tag.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$last_name</td>
							<td>string</td>
							<td>Last Name</td>
						</tr><tr>
							<td>$order</td>
							<td>WC_Order|WC_Order_Refund</td>
							<td>Order</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
add_filter( 'convertkit_for_woocommerce_last_name', function( $last_name, $order ) {
	// ... your code here
	// Return value
	return $last_name;
}, 10, 2 );
</pre>
<h1>Actions</h1><table>
				<thead>
					<tr>
						<th>File</th>
						<th>Filter Name</th>
						<th>Description</th>
					</tr>
				</thead>
				<tbody><tr>
						<td colspan="3">../includes/class-ckwc-api.php</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_api_form_subscribe_success"><code>convertkit_api_form_subscribe_success</code></a></td>
						<td>Runs actions immediately after the email address was successfully subscribed to the form.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_api_sequence_subscribe_success"><code>convertkit_api_sequence_subscribe_success</code></a></td>
						<td>Runs actions immediately after the email address was successfully subscribed to the sequence.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_api_tag_subscribe_success"><code>convertkit_api_tag_subscribe_success</code></a></td>
						<td>Runs actions immediately after the email address was successfully subscribed to the tag.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_api_form_unsubscribe_success"><code>convertkit_api_form_unsubscribe_success</code></a></td>
						<td>Runs actions immediately after the email address was successfully unsubscribed.</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_api_purchase_create_success"><code>convertkit_api_purchase_create_success</code></a></td>
						<td>Runs actions immediately after the purchase data address was successfully created.</td>
					</tr><tr>
						<td colspan="3">../includes/class-wp-ckwc.php</td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_initialize_admin"><code>convertkit_for_woocommerce_initialize_admin</code></a></td>
						<td></td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_initialize_frontend"><code>convertkit_for_woocommerce_initialize_frontend</code></a></td>
						<td></td>
					</tr><tr>
						<td>&nbsp;</td>
						<td><a href="#convertkit_for_woocommerce_initialize_global"><code>convertkit_for_woocommerce_initialize_global</code></a></td>
						<td></td>
					</tr>
					</tbody>
				</table><h3 id="convertkit_api_form_subscribe_success">
						convertkit_api_form_subscribe_success
						<code>includes/class-ckwc-api.php::193</code>
					</h3><h4>Overview</h4>
						<p>Runs actions immediately after the email address was successfully subscribed to the form.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$response</td>
							<td>array</td>
							<td>API Response</td>
						</tr><tr>
							<td>$form_id</td>
							<td>string</td>
							<td>Form ID</td>
						</tr><tr>
							<td>$email</td>
							<td>string</td>
							<td>Email Address</td>
						</tr><tr>
							<td>$first_name</td>
							<td>string</td>
							<td>First</td>
						</tr><tr>
							<td>$fields</td>
							<td>mixed</td>
							<td>Custom Fields (false|array).</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_api_form_subscribe_success', function( $response, $form_id, $email, $first_name, $fields ) {
	// ... your code here
}, 10, 5 );
</pre>
<h3 id="convertkit_api_sequence_subscribe_success">
						convertkit_api_sequence_subscribe_success
						<code>includes/class-ckwc-api.php::309</code>
					</h3><h4>Overview</h4>
						<p>Runs actions immediately after the email address was successfully subscribed to the sequence.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$response</td>
							<td>array</td>
							<td>API Response</td>
						</tr><tr>
							<td>$sequence_id</td>
							<td>string</td>
							<td>Sequence ID</td>
						</tr><tr>
							<td>$email</td>
							<td>string</td>
							<td>Email Address</td>
						</tr><tr>
							<td>$fields</td>
							<td>mixed</td>
							<td>Custom Fields (false|array)</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_api_sequence_subscribe_success', function( $response, $sequence_id, $email, $fields ) {
	// ... your code here
}, 10, 4 );
</pre>
<h3 id="convertkit_api_tag_subscribe_success">
						convertkit_api_tag_subscribe_success
						<code>includes/class-ckwc-api.php::401</code>
					</h3><h4>Overview</h4>
						<p>Runs actions immediately after the email address was successfully subscribed to the tag.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$response</td>
							<td>array</td>
							<td>API Response</td>
						</tr><tr>
							<td>$tag_id</td>
							<td>string</td>
							<td>Tag ID</td>
						</tr><tr>
							<td>$email</td>
							<td>string</td>
							<td>Email Address</td>
						</tr><tr>
							<td>$fields</td>
							<td>mixed</td>
							<td>Custom Fields (false|array).</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_api_tag_subscribe_success', function( $response, $tag_id, $email, $fields ) {
	// ... your code here
}, 10, 4 );
</pre>
<h3 id="convertkit_api_form_unsubscribe_success">
						convertkit_api_form_unsubscribe_success
						<code>includes/class-ckwc-api.php::601</code>
					</h3><h4>Overview</h4>
						<p>Runs actions immediately after the email address was successfully unsubscribed.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$response</td>
							<td>array</td>
							<td>API Response</td>
						</tr><tr>
							<td>$email</td>
							<td>string</td>
							<td>Email Address</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_api_form_unsubscribe_success', function( $response, $email ) {
	// ... your code here
}, 10, 2 );
</pre>
<h3 id="convertkit_api_purchase_create_success">
						convertkit_api_purchase_create_success
						<code>includes/class-ckwc-api.php::735</code>
					</h3><h4>Overview</h4>
						<p>Runs actions immediately after the purchase data address was successfully created.</p><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody><tr>
							<td>$response</td>
							<td>array</td>
							<td>API Response</td>
						</tr><tr>
							<td>$purchase</td>
							<td>array</td>
							<td>Purchase Data</td>
						</tr>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_api_purchase_create_success', function( $response, $purchase ) {
	// ... your code here
}, 10, 2 );
</pre>
<h3 id="convertkit_for_woocommerce_initialize_admin">
						convertkit_for_woocommerce_initialize_admin
						<code>includes/class-wp-ckwc.php::109</code>
					</h3><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_for_woocommerce_initialize_admin', function(  ) {
	// ... your code here
}, 10, 0 );
</pre>
<h3 id="convertkit_for_woocommerce_initialize_frontend">
						convertkit_for_woocommerce_initialize_frontend
						<code>includes/class-wp-ckwc.php::132</code>
					</h3><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_for_woocommerce_initialize_frontend', function(  ) {
	// ... your code here
}, 10, 0 );
</pre>
<h3 id="convertkit_for_woocommerce_initialize_global">
						convertkit_for_woocommerce_initialize_global
						<code>includes/class-wp-ckwc.php::151</code>
					</h3><h4>Parameters</h4>
					<table>
						<thead>
							<tr>
								<th>Parameter</th>
								<th>Type</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table><h4>Usage</h4>
<pre>
do_action( 'convertkit_for_woocommerce_initialize_global', function(  ) {
	// ... your code here
}, 10, 0 );
</pre>
