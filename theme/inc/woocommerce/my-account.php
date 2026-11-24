<?php
/**
 * My Account: content titles, auth wrapper, /login and /register redirects.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 8  My Account
   ============================================================================= */

/**
 * Insert Wishlist between Dashboard and Orders in My Account nav.
 */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	$new_items = array();
	foreach ( $items as $key => $label ) {
		$new_items[ $key ] = $label;
		if ( 'dashboard' === $key ) {
			$new_items['wishlist'] = __( 'Wishlist', 'shopchop' );
		}
	}
	return $new_items;
} );

add_filter( 'woocommerce_get_endpoint_url', function ( $url, $endpoint ) {
	if ( 'wishlist' === $endpoint && function_exists( 'YITH_WCWL' ) ) {
		$page_id = YITH_WCWL()->get_wishlist_page_id();
		if ( $page_id ) {
			$url = get_permalink( $page_id );
		}
	}
	return $url;
}, 10, 2 );

/**
 * Insert a contextual <h1> title at the top of every My Account content area.
 */
add_action( 'woocommerce_account_content', 'shopchop_account_content_title', 1 );
function shopchop_account_content_title() {
	$endpoint_titles = array(
		'orders'       => 'Orders',
		'downloads'    => 'Downloads',
		'view-order'   => 'Order Details',
		'edit-address'  => 'Addresses',
		'edit-account'  => 'Account Details',
		'pool-profile'  => 'Pool Profile',
	);
	
	$title = 'Dashboard'; // default
	foreach ( $endpoint_titles as $endpoint => $label ) {
		if ( is_wc_endpoint_url( $endpoint ) ) {
			$title = $label;
			break;
		}
	}

	echo '<h1 class="account-content-title">' . esc_html( $title ) . '</h1>';
}



/**
 * Wrap all authentication forms (login, register, lost/reset password)
 * in a shared <div class="wc-auth-wrapper"> for consistent styling.
 */
function shopchop_auth_wrapper_start() { echo '<div class="wc-auth-wrapper">'; }
function shopchop_auth_wrapper_end()   { echo '</div>'; }

add_action( 'woocommerce_before_customer_login_form',          'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_customer_login_form',           'shopchop_auth_wrapper_end'     );
add_action( 'woocommerce_before_lost_password_form',           'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_lost_password_form',            'shopchop_auth_wrapper_end'     );
add_action( 'woocommerce_before_reset_password_form',          'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_reset_password_form',           'shopchop_auth_wrapper_end'     );
add_action( 'woocommerce_before_lost_password_confirmation_message', 'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_lost_password_confirmation_message',  'shopchop_auth_wrapper_end'     );



/**
 * Redirect bare /login and /register slugs to the WooCommerce My Account page.
 * Only fires for non-logged-in users.
 */
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) return;

	$path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
	if ( in_array( $path, array( 'login', 'register' ), true ) ) {
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}
} );
