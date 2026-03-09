<?php
/**
 * The header for our theme
 *
 * This is the template that displays the `head` element and everything up
 * until the `#content` element.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package ShopChop
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<div id="page">
	<a href="#content" class="sr-only"><?php esc_html_e( 'Skip to content', 'shopchop' ); ?></a>

	<?php
	get_template_part( 'template-parts/layout/header', 'content' );

	$is_minimal_page = is_checkout() || is_wc_endpoint_url('order-received') || (is_account_page() && !is_user_logged_in()) || is_lost_password_page();
	$is_homepage = is_front_page(); 
	
	$body_class = $is_minimal_page ? 'shopchop-body-minimal' : 'shopchop-body-normal';
	$homepage_class = $is_homepage ? 'shopchop-homepage-wrapper' : '';
	?>

	<div id="content" class="shopchop-main-content-wrapper <?php echo $body_class; ?> <?php echo $homepage_class; ?>">
