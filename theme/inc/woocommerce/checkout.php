<?php
/**
 * Checkout: address fields and the thank-you page next steps.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 5  Checkout & Address Fields
   ============================================================================= */

/**
 * Remove Select2 (we use native <select> elements styled via CSS).
 *
 * @param array $enqueue_styles Registered WooCommerce styles.
 * @return array Unchanged (removal happens via wp_dequeue_*).
 */
add_filter( 'woocommerce_enqueue_styles', 'shopchop_disable_select2', 9999 );
function shopchop_disable_select2( $enqueue_styles ) {
	wp_dequeue_style( 'select2' );
	wp_deregister_style( 'select2' );
	wp_dequeue_script( 'selectWoo' );
	wp_deregister_script( 'selectWoo' );
	return $enqueue_styles;
}



/**
 * Customise default address fields:
 * – Remove last_name and address_2.
 * – Relabel remaining fields for a Malaysian audience.
 * – Reorder fields for a cleaner checkout flow.
 *
 * @param array $fields Default address fields.
 * @return array Modified fields.
 */
add_filter( 'woocommerce_default_address_fields', 'shopchop_override_address_fields' );
function shopchop_override_address_fields( $fields ) {
	unset( $fields['last_name'] );
	unset( $fields['address_2'] );

	// Labels
	$fields['first_name']['label'] = __( 'Name',     'woocommerce' );
	$fields['country']['label']    = __( 'Country',  'woocommerce' );
	$fields['address_1']['label']  = __( 'Address',  'woocommerce' );
	$fields['city']['label']       = __( 'City',     'woocommerce' );
	$fields['state']['label']      = __( 'State',    'woocommerce' );
	$fields['postcode']['label']   = __( 'Postcode', 'woocommerce' );
	$fields['phone']['label']      = __( 'Phone',    'woocommerce' );
	$fields['email']['label']      = __( 'Email',    'woocommerce' );

	// Autocomplete hint
	$fields['first_name']['autocomplete'] = 'name';

	// Placeholders (Malaysia-specific examples)
	$fields['first_name']['placeholder'] = __( 'Name',                                   'woocommerce' );
	$fields['address_1']['placeholder']  = __( '3, Jalan Pembangunan, Taman Perumahan',  'woocommerce' );
	$fields['city']['placeholder']       = __( 'Johor Bahru',                            'woocommerce' );
	$fields['postcode']['placeholder']        = __( '80000', 'woocommerce' );
	$fields['phone']['placeholder']           = __( '+60123456789', 'woocommerce' );
	$fields['email']['placeholder']           = __( 'mail@example.com', 'woocommerce' );
	$fields['postcode']['custom_attributes']  = [ 'maxlength' => '5', 'inputmode' => 'numeric', 'pattern' => '[0-9]{5}' ];
	$fields['phone']['custom_attributes']     = [ 'inputmode' => 'tel' ];

	return $fields;
}



/**
 * Remove last_name from the required-fields list on the Edit Account screen.
 *
 * @param array $fields Required account fields.
 * @return array Modified fields.
 */
add_filter( 'woocommerce_save_account_details_required_fields', 'shopchop_remove_last_name_field' );
function shopchop_remove_last_name_field( $fields ) {
	unset( $fields['account_last_name'] );
	return $fields;
}



/**
 * Reorder address fields so postcode appears before city.
 *
 * @param array $fields Address fields.
 * @return array Fields with updated priorities.
 */
add_filter( 'woocommerce_default_address_fields', 'shopchop_reorder_fields' );
function shopchop_reorder_fields( $fields ) {
	$fields['first_name']['priority'] = 10;
	$fields['address_1']['priority']  = 20;
	$fields['postcode']['priority']   = 30;
	$fields['city']['priority']       = 40;
	$fields['state']['priority']      = 50;
	$fields['country']['priority']    = 60;
	return $fields;
}



/**
 * Rename cart/checkout shipping package label from WC core's default
 * "Shipment" to "Shipping".
 *
 * @param string $shipping_package_name Default package name.
 * @return string Renamed package name.
 */
add_filter( 'woocommerce_shipping_package_name', 'shopchop_rename_shipping_package', 10, 1 );
function shopchop_rename_shipping_package( $shipping_package_name ) {
	return __( 'Shipping', 'shopchop' );
}


/* =============================================================================
	§ 11 Thank-You Page
   ============================================================================= */

/**
 * Append a "What to do Next?" section below the standard order confirmation.
 *
 * @param int $order_id The newly placed order's ID.
 */
add_action( 'woocommerce_thankyou', 'shopchop_add_next_steps', 10 );
function shopchop_add_next_steps( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) return;
	?>
	<section class="wc-next-steps-order">
		<h2 class="wc-next-steps-title">Next steps</h2>
		<ul class="space-y-2">
			<li class="flex items-start gap-2">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true" class="shrink-0 mt-0.5"><path d="M128,96a32,32,0,1,0,32,32A32,32,0,0,0,128,96Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,128,144Z"></path></svg>
				<span><strong>Order confirmation:</strong> We'll email you when your order ships.</span>
			</li>
			<li class="flex items-start gap-2">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true" class="shrink-0 mt-0.5"><path d="M128,96a32,32,0,1,0,32,32A32,32,0,0,0,128,96Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,128,144Z"></path></svg>
				<span><strong>Track your package:</strong> We'll email your tracking number once we dispatch.</span>
			</li>
			<li class="flex items-start gap-2">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true" class="shrink-0 mt-0.5"><path d="M128,96a32,32,0,1,0,32,32A32,32,0,0,0,128,96Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,128,144Z"></path></svg>
				<span><strong>Need help?</strong> Reach us on
				<a href="<?php echo esc_url( 'https://wa.me/' . shopchop_get_whatsapp_number() . '?text=' . rawurlencode( 'Hi, I need help with my order: #' . $order->get_order_number() ) ); ?>" class="underline! font-bold text-primary-900" target="_blank" rel="noreferrer">WhatsApp</a>
				or
				<a href="mailto:<?php echo esc_attr( shopchop_get_shop_email() ); ?>" class="underline! font-bold text-primary-900" rel="noreferrer"><?php echo esc_html( shopchop_get_shop_email() ); ?></a>
				with Order ID <strong><?php echo esc_html( $order->get_order_number() ); ?></strong></span>
			</li>
		</ul>
	</section>
	<?php
}

/**
 * Checkout coupon notice: the link's visible text is its name (core pairs
 * "Click here to enter your code" with a different aria-label).
 */
function shopchop_checkout_coupon_message() {
	return esc_html__( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" role="button" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false" class="showcoupon">' . esc_html__( 'Enter your coupon code', 'woocommerce' ) . '</a>';
}
add_filter( 'woocommerce_checkout_coupon_message', 'shopchop_checkout_coupon_message' );
