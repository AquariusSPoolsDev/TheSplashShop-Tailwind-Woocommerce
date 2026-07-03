<?php
/**
 * ShopChop functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ShopChop
 */

if ( ! defined( 'SHOPCHOP_VERSION' ) ) {
	/*
	 * Set the theme’s version number.
	 *
	 * This is used primarily for cache busting. If you use `npm run bundle`
	 * to create your production build, the value below will be replaced in the
	 * generated zip file with a timestamp, converted to base 36.
	 */
	define( 'SHOPCHOP_VERSION', '0.1.1' );
}

if ( ! defined( 'SHOPCHOP_TYPOGRAPHY_CLASSES' ) ) {
	/*
	 * Set Tailwind Typography classes for the front end, block editor and
	 * classic editor using the constant below.
	 *
	 * For the front end, these classes are added by the `shopchop_content_class`
	 * function. You will see that function used everywhere an `entry-content`
	 * or `page-content` class has been added to a wrapper element.
	 *
	 * For the block editor, these classes are converted to a JavaScript array
	 * and then used by the `./javascript/block-editor.js` file, which adds
	 * them to the appropriate elements in the block editor (and adds them
	 * again when they’re removed.)
	 *
	 * For the classic editor (and anything using TinyMCE, like Advanced Custom
	 * Fields), these classes are added to TinyMCE’s body class when it
	 * initializes.
	 */
	define(
		'SHOPCHOP_TYPOGRAPHY_CLASSES',
		'prose prose-shopchop max-w-none prose-a:text-primary'
	);
}

if ( ! function_exists( 'shopchop_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function shopchop_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on ShopChop, use a find and replace
		 * to change 'shopchop' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'shopchop', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'menu-1'        => __( 'Main Navigation Menu', 'shopchop' ),
				'footer-1-menu' => __( 'Footer Menu #1', 'shopchop' ),
				'footer-2-menu' => __( 'Footer Menu #2', 'shopchop' ),
				'footer-3-menu' => __( 'Footer Menu #3', 'shopchop' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Custom Logo
		add_theme_support( 'custom-logo' );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );

		// Remove support for block templates.
		remove_theme_support( 'block-templates' );

		// CUSTOM: Add support for WooCommerce
		add_theme_support( 'woocommerce' );

		// CUSTOM: Add support for WC product gallery
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		// wc-product-gallery-slider intentionally omitted — Swiper handles navigation
	}
endif;
add_action( 'after_setup_theme', 'shopchop_setup' );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function shopchop_widgets_init() {
	// register_sidebar(
	// 	array(
	// 		'name'          => __( 'Footer', 'shopchop' ),
	// 		'id'            => 'sidebar-1',
	// 		'description'   => __( 'Add widgets here to appear in your footer.', 'shopchop' ),
	// 		'before_widget' => '<section id="%1$s" class="widget %2$s">',
	// 		'after_widget'  => '</section>',
	// 		'before_title'  => '<h2 class="widget-title">',
	// 		'after_title'   => '</h2>',
	// 	)
	// );

	// Footer Contents
	register_sidebar(
		array(
			'name'          => __( 'Footer Content 0', 'shopchop' ),
			'id'            => 'footer-content-0',
			'description'   => __( 'Social icons — displayed below the logo and site tagline in the footer brand column.', 'shopchop' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="widget-title">',
			'after_title'   => '</h5>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Content 1', 'shopchop' ),
			'id'            => 'footer-content-1',
			'description'   => __( 'Main navigation links — primary site menu displayed in the footer.', 'shopchop' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="widget-title">',
			'after_title'   => '</h5>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Content 2', 'shopchop' ),
			'id'            => 'footer-content-2',
			'description'   => __( 'Extra navigation menu — secondary links such as categories, collections, or support pages.', 'shopchop' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="widget-title">',
			'after_title'   => '</h5>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Content 3', 'shopchop' ),
			'id'            => 'footer-content-3',
			'description'   => __( 'Legal links — Privacy Policy, Return Policy, Cookie Policy, and similar pages. Displayed in the bottom bar above the copyright line.', 'shopchop' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="widget-title">',
			'after_title'   => '</h5>',
		)
	);
}
add_action( 'widgets_init', 'shopchop_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function shopchop_scripts() {
	$ver = ( defined( 'WP_DEBUG' ) && WP_DEBUG )
		? filemtime( get_template_directory() . '/style.css' )
		: SHOPCHOP_VERSION;

	wp_enqueue_style( 'shopchop-style', get_stylesheet_uri(), array(), $ver );
	wp_enqueue_style( 'shopchop-fonts', 'https://fonts.bunny.net/css?family=manrope:300,400,500,600,700,800', array(), null );
	wp_enqueue_script( 'shopchop-script', get_template_directory_uri() . '/js/script.min.js', array( 'jquery' ), $ver, true );

	if ( is_woocommerce() || is_front_page() || is_home() ) {
		wp_enqueue_style( 'shopchop-swiper', 'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css', array(), $ver );
		wp_enqueue_script( 'shopchop-swiper', 'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js', array(), $ver, true );
		wp_enqueue_style( 'shopchop-glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css', array(), $ver );
		wp_enqueue_script( 'shopchop-glightbox', 'https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js', array(), $ver, true );
	}

	if ( is_product() ) {
		wp_enqueue_script( 'shopchop-medium-zoom', 'https://cdnjs.cloudflare.com/ajax/libs/medium-zoom/1.1.0/medium-zoom.min.js', array(), $ver, true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Localize script for AJAX search
	wp_localize_script('shopchop-script', 'shopchopDynamicSearch', array(
		'ajax_url'   => admin_url('admin-ajax.php'),
		'nonce'      => wp_create_nonce('wc_ajax_search_nonce'),
		'cart_nonce' => wp_create_nonce('shopchop_cart_nonce'),
	));

	// Localize postcode lookup only on checkout and edit-address pages
	if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() || is_wc_endpoint_url( 'edit-address' ) ) ) {
		wp_localize_script( 'shopchop-script', 'shopchopPostcodes', shopchop_build_postcode_lookup() );
	}
}
add_action( 'wp_enqueue_scripts', 'shopchop_scripts' );

function shopchop_load_postcode_json() {
	static $data = null;
	if ( $data !== null ) return $data;

	$json_path = get_template_directory() . '/data/my-postcodes.json';
	if ( ! file_exists( $json_path ) ) return ( $data = [] );

	$decoded = json_decode( file_get_contents( $json_path ), true );
	$data    = ( $decoded && ! empty( $decoded['states'] ) ) ? $decoded : [];
	return $data;
}

function shopchop_build_postcode_lookup() {
	$data = shopchop_load_postcode_json();
	if ( empty( $data ) ) return [];

	// WooCommerce uses SWK for Sarawak; JSON has SRW
	$code_map = [ 'SRW' => 'SWK' ];

	$lookup = [];
	foreach ( $data['states'] as $state ) {
		$state_code = $code_map[ $state['code'] ] ?? $state['code'];
		foreach ( $state['cities'] as $city ) {
			foreach ( $city['postcodes'] as $postcode ) {
				if ( ! isset( $lookup[ $postcode ] ) ) {
					$lookup[ $postcode ] = [
						'city'  => $city['name'],
						'state' => $state_code,
					];
				}
			}
		}
	}
	return $lookup;
}

// MY address format: "79000 Iskandar Puteri, Johor" (postcode + city on line 1, state on line 2)
add_filter( 'woocommerce_localisation_address_formats', function ( $formats ) {
	$formats['MY'] = "{name}\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{state}\n{country}";
	return $formats;
} );

// Override WooCommerce Malaysia state list with names from the JSON dataset
add_filter( 'woocommerce_states', function ( $states ) {
	$data = shopchop_load_postcode_json();
	if ( empty( $data ) ) return $states;

	$code_map = [ 'SRW' => 'SWK' ];
	$name_map = [
		'KUL' => 'W.P. Kuala Lumpur',
		'LBN' => 'W.P. Labuan',
		'PJY' => 'W.P. Putrajaya',
	];

	$my_states = [];
	foreach ( $data['states'] as $state ) {
		$code               = $code_map[ $state['code'] ] ?? $state['code'];
		$my_states[ $code ] = $name_map[ $code ] ?? $state['name'];
	}

	$states['MY'] = $my_states;
	return $states;
} );

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.bunny.net">' . "\n";
}, 1 );

/**
 * Enqueue the block editor script.
 */
function shopchop_enqueue_block_editor_script() {
	$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if (
		$current_screen &&
		$current_screen->is_block_editor() &&
		'widgets' !== $current_screen->id
	) {
		wp_enqueue_script(
			'shopchop-editor',
			get_template_directory_uri() . '/js/block-editor.min.js',
			array(
				'wp-blocks',
				'wp-edit-post',
			),
			SHOPCHOP_VERSION,
			true
		);
		wp_add_inline_script( 'shopchop-editor', "tailwindTypographyClasses = '" . esc_attr( SHOPCHOP_TYPOGRAPHY_CLASSES ) . "'.split(' ');", 'before' );
	}
}
add_action( 'enqueue_block_assets', 'shopchop_enqueue_block_editor_script' );

/**
 * Add the Tailwind Typography classes to TinyMCE.
 *
 * @param array $settings TinyMCE settings.
 * @return array
 */
function shopchop_tinymce_add_class( $settings ) {
	$settings['body_class'] = SHOPCHOP_TYPOGRAPHY_CLASSES;
	return $settings;
}
add_filter( 'tiny_mce_before_init', 'shopchop_tinymce_add_class' );

/**
 * Limit the block editor to heading levels supported by Tailwind Typography.
 *
 * @param array  $args Array of arguments for registering a block type.
 * @param string $block_type Block type name including namespace.
 * @return array
 */
function shopchop_modify_heading_levels( $args, $block_type ) {
	if ( 'core/heading' !== $block_type ) {
		return $args;
	}

	// Remove <h1>, <h5> and <h6>.
	$args['attributes']['levelOptions']['default'] = array( 2, 3, 4 );

	return $args;
}
add_filter( 'register_block_type_args', 'shopchop_modify_heading_levels', 10, 2 );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';
