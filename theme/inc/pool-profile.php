<?php
/**
 * Pool Profile
 *
 * Lets logged-in customers save one or more pool profiles under My Account.
 * Stored as a private `pool_profile` CPT, one post per profile, owned via
 * post_author.
 *
 * @package shopchop
 */

defined( 'ABSPATH' ) || exit;

define( 'SHOPCHOP_POOL_TYPES', array(
	'concrete'   => __( 'Concrete', 'shopchop' ),
	'fibreglass' => __( 'Fibreglass', 'shopchop' ),
	'vinyl'      => __( 'Vinyl liner', 'shopchop' ),
) );

define( 'SHOPCHOP_POOL_SHAPES', array(
	'rectangular' => __( 'Rectangular', 'shopchop' ),
	'freeform'    => __( 'Freeform', 'shopchop' ),
	'oval'        => __( 'Oval / rounded', 'shopchop' ),
	'l-shape'     => __( 'L-shape', 'shopchop' ),
) );

define( 'SHOPCHOP_SANITISER_METHODS', array(
	'chlorine'  => __( 'Chlorine', 'shopchop' ),
	'saltwater' => __( 'Saltwater', 'shopchop' ),
) );

define( 'SHOPCHOP_SKIMMER_TYPES', array(
	'skimmer'  => __( 'Skimmer', 'shopchop' ),
	'overflow' => __( 'Overflow', 'shopchop' ),
	'infinity' => __( 'Infinity', 'shopchop' ),
) );

define( 'SHOPCHOP_FILTER_TYPES', array(
	'sand'      => __( 'Sand filter', 'shopchop' ),
	'cartridge' => __( 'Cartridge filter', 'shopchop' ),
) );

define( 'SHOPCHOP_EQUIPMENT_BRANDS', array(
	'waterco' => __( 'Waterco', 'shopchop' ),
	'astral'  => __( 'Astral', 'shopchop' ),
	'minder'  => __( 'Minder', 'shopchop' ),
	'pentair' => __( 'Pentair', 'shopchop' ),
	'davey'   => __( 'Davey', 'shopchop' ),
	'other'   => __( 'Other', 'shopchop' ),
) );

/**
 * Register the pool_profile CPT. Front-end create/edit/delete is handled by
 * our own ownership checks (post_author === current user), not WP capabilities —
 * customers don't have edit_posts. No wp-admin UI/menu — the team reviews
 * profiles via the "Customer Pool Profiles" tab in ShopChop Theme Settings
 * instead (queries this post type directly, doesn't need the raw CPT screens).
 */
add_action( 'init', 'shopchop_register_pool_profile_cpt' );
function shopchop_register_pool_profile_cpt() {
	register_post_type( 'pool_profile', array(
		'label'        => __( 'Pool Profiles', 'shopchop' ),
		'public'       => false,
		'show_ui'      => false,
		'show_in_menu' => false,
		'supports'     => array( 'title' ),
	) );
}

/**
 * My Account "pool-profile" endpoint.
 */
add_action( 'init', function() {
	add_rewrite_endpoint( 'pool-profile', EP_ROOT | EP_PAGES );
} );

add_filter( 'woocommerce_get_query_vars', function( $vars ) {
	$vars['pool-profile'] = 'pool-profile';
	return $vars;
} );

// Rewrite rules need flushing once after the endpoint is registered.
add_action( 'init', function() {
	if ( ! get_option( 'shopchop_pool_profile_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'shopchop_pool_profile_flushed', 1 );
	}
}, 20 );

add_filter( 'woocommerce_account_menu_items', function( $items ) {
	$new_items = array();
	foreach ( $items as $key => $label ) {
		if ( 'orders' === $key ) {
			$new_items['pool-profile'] = __( 'Pool Profile', 'shopchop' );
		}
		$new_items[ $key ] = $label;
	}
	return $new_items;
}, 20 );

add_action( 'woocommerce_account_pool-profile_endpoint', 'shopchop_pool_profile_endpoint_content' );
function shopchop_pool_profile_endpoint_content() {
	wc_get_template( 'myaccount/pool-profile.php' );
}

/**
 * Fetch the current user's saved pool profiles.
 *
 * @return WP_Post[]
 */
function shopchop_get_pool_profiles() {
	if ( ! is_user_logged_in() ) {
		return array();
	}

	return get_posts( array(
		'post_type'      => 'pool_profile',
		'post_status'    => 'publish',
		'author'         => get_current_user_id(),
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
	) );
}

/**
 * Handle add / edit / delete form submissions. Runs before any output so we
 * can redirect back to the endpoint afterwards.
 */
add_action( 'template_redirect', 'shopchop_handle_pool_profile_actions' );
function shopchop_handle_pool_profile_actions() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$account_url = wc_get_account_endpoint_url( 'pool-profile' );

	// Save (add or update).
	if ( isset( $_POST['shopchop_pool_profile_save'] ) && check_admin_referer( 'shopchop_save_pool_profile', 'shopchop_pool_profile_nonce' ) ) {
		$profile_id = isset( $_POST['profile_id'] ) ? absint( $_POST['profile_id'] ) : 0;
		$name       = isset( $_POST['pool_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pool_name'] ) ) : '';
		$volume     = isset( $_POST['pool_volume'] ) ? absint( $_POST['pool_volume'] ) : 0;
		$type       = isset( $_POST['pool_type'] ) ? sanitize_text_field( wp_unslash( $_POST['pool_type'] ) ) : '';

		if ( '' === $name || $volume <= 0 || ! array_key_exists( $type, SHOPCHOP_POOL_TYPES ) ) {
			wc_add_notice( __( 'Please fill in pool name, volume, and pool type.', 'shopchop' ), 'error' );
			return;
		}

		// Editing an existing profile — confirm ownership first.
		if ( $profile_id ) {
			$existing = get_post( $profile_id );
			if ( ! $existing || 'pool_profile' !== $existing->post_type || (int) $existing->post_author !== get_current_user_id() ) {
				wc_add_notice( __( 'Pool profile not found.', 'shopchop' ), 'error' );
				wp_safe_redirect( $account_url );
				exit;
			}
		}

		$post_id = wp_insert_post( array(
			'ID'          => $profile_id ?: 0,
			'post_type'   => 'pool_profile',
			'post_status' => 'publish',
			'post_title'  => $name,
			'post_author' => get_current_user_id(),
		) );

		if ( ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_pool_volume', $volume );
			update_post_meta( $post_id, '_pool_type', $type );

			// Optional dimensions (kept for reference / re-editing the calculator).
			foreach ( array( 'pool_size_l', 'pool_size_w', 'pool_size_h' ) as $dim ) {
				$value = isset( $_POST[ $dim ] ) ? sanitize_text_field( wp_unslash( $_POST[ $dim ] ) ) : '';
				update_post_meta( $post_id, '_' . $dim, is_numeric( $value ) ? floatval( $value ) : '' );
			}

			// Optional pool characteristics.
			$shape     = isset( $_POST['pool_shape'] ) ? sanitize_text_field( wp_unslash( $_POST['pool_shape'] ) ) : '';
			$sanitiser = isset( $_POST['sanitiser_method'] ) ? sanitize_text_field( wp_unslash( $_POST['sanitiser_method'] ) ) : '';
			$skimmer   = isset( $_POST['skimmer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['skimmer_type'] ) ) : '';

			update_post_meta( $post_id, '_pool_shape', array_key_exists( $shape, SHOPCHOP_POOL_SHAPES ) ? $shape : '' );
			update_post_meta( $post_id, '_sanitiser_method', array_key_exists( $sanitiser, SHOPCHOP_SANITISER_METHODS ) ? $sanitiser : '' );
			update_post_meta( $post_id, '_skimmer_type', array_key_exists( $skimmer, SHOPCHOP_SKIMMER_TYPES ) ? $skimmer : '' );

			// Optional equipment details.
			$pump    = isset( $_POST['pump_used'] ) ? sanitize_text_field( wp_unslash( $_POST['pump_used'] ) ) : '';
			$filter  = isset( $_POST['filter_type'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_type'] ) ) : '';
			$brand   = isset( $_POST['equipment_brand'] ) ? sanitize_text_field( wp_unslash( $_POST['equipment_brand'] ) ) : '';
			$service = isset( $_POST['last_service_date'] ) ? sanitize_text_field( wp_unslash( $_POST['last_service_date'] ) ) : '';
			$notes   = isset( $_POST['pool_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pool_notes'] ) ) : '';

			update_post_meta( $post_id, '_pump_used', $pump );
			update_post_meta( $post_id, '_filter_type', array_key_exists( $filter, SHOPCHOP_FILTER_TYPES ) ? $filter : '' );
			update_post_meta( $post_id, '_equipment_brand', array_key_exists( $brand, SHOPCHOP_EQUIPMENT_BRANDS ) ? $brand : '' );
			update_post_meta( $post_id, '_last_service_date', $service );
			update_post_meta( $post_id, '_pool_notes', $notes );

			// Optional photo upload — images only, max 10MB.
			if ( ! empty( $_FILES['pool_photo']['name'] ) ) {
				$max_size = 10 * MB_IN_BYTES;

				if ( isset( $_FILES['pool_photo']['size'] ) && (int) $_FILES['pool_photo']['size'] > $max_size ) {
					wc_add_notice( __( 'Pool photo is too large. Maximum file size is 10MB.', 'shopchop' ), 'error' );
				} else {
					require_once ABSPATH . 'wp-admin/includes/image.php';
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';

					$attachment_id = media_handle_upload( 'pool_photo', $post_id, array(), array(
						'test_form' => false,
						'mimes'     => array(
							'jpg|jpeg' => 'image/jpeg',
							'png'      => 'image/png',
							'heic'     => 'image/heic',
						),
					) );

					if ( is_wp_error( $attachment_id ) ) {
						wc_add_notice( __( 'Pool photo must be a JPG, PNG, or HEIC image.', 'shopchop' ), 'error' );
					} else {
						$previous = (int) get_post_meta( $post_id, '_pool_photo_id', true );
						update_post_meta( $post_id, '_pool_photo_id', $attachment_id );
						if ( $previous && $previous !== (int) $attachment_id ) {
							wp_delete_attachment( $previous, true );
						}
					}
				}
			}

			wc_add_notice( $profile_id ? __( 'Pool profile updated.', 'shopchop' ) : __( 'Pool profile added.', 'shopchop' ) );
		}

		wp_safe_redirect( $account_url );
		exit;
	}

	// Delete.
	if ( isset( $_GET['shopchop_delete_pool'] ) && isset( $_GET['_wpnonce'] ) ) {
		$profile_id = absint( $_GET['shopchop_delete_pool'] );

		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'shopchop_delete_pool_profile_' . $profile_id ) ) {
			$existing = get_post( $profile_id );
			if ( $existing && 'pool_profile' === $existing->post_type && (int) $existing->post_author === get_current_user_id() ) {
				$photo_id = get_post_meta( $profile_id, '_pool_photo_id', true );
				if ( $photo_id ) {
					wp_delete_attachment( $photo_id, true );
				}
				wp_delete_post( $profile_id, true );
				wc_add_notice( __( 'Pool profile deleted.', 'shopchop' ) );
			}
		}

		wp_safe_redirect( $account_url );
		exit;
	}
}
