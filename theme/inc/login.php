<?php
/**
 * Login: generic error messages and the wp-login.php styling.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 10 Authentication
   ============================================================================= */

/**
 * Return a generic error message on failed login to avoid username enumeration.
 *
 * @param WP_User|WP_Error $user     Authentication result.
 * @param string           $username Submitted username.
 * @param string           $password Submitted password.
 * @return WP_User|WP_Error
 */
add_filter( 'authenticate', 'shopchop_remove_login_errors', 20, 3 );
function shopchop_remove_login_errors( $user, $username, $password ) {
	if ( ! empty( $username ) && ! empty( $password ) && is_wp_error( $user ) ) {
		return new WP_Error(
			'authentication_failed',
			__( '<strong>Error</strong>: Invalid username or password. Please try again.' )
		);
	}
	return $user;
}


/* =============================================================================
   § 19 — Admin Login Page Styling
   ============================================================================= */

/**
 * Enqueue custom CSS on the WordPress login screen.
 * Same WP auth — purely cosmetic override.
 */
add_action( 'login_enqueue_scripts', 'shopchop_login_styles' );
function shopchop_login_styles() {
	wp_enqueue_style( 'shopchop-login-fonts', 'https://fonts.bunny.net/css?family=manrope:400,600,700,800', array(), null );
	wp_enqueue_style( 'shopchop-login', get_template_directory_uri() . '/assets/css/login.css', array( 'login', 'shopchop-login-fonts' ), SHOPCHOP_VERSION );
}

/** Point the login logo link back to the site home. */
add_filter( 'login_headerurl', fn() => home_url() );

/** Update the logo link title attribute. */
add_filter( 'login_headertext', fn() => get_bloginfo( 'name' ) );
