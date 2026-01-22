<?php
/**
 * Template part for displaying the footer content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ShopChop
 */

// Check for Checkout, Order Received, Login/Register, and Lost Password
$is_minimal_page = is_checkout() ||
	is_wc_endpoint_url('order-received') ||
	(is_account_page() && !is_user_logged_in()) ||
	is_lost_password_page(); // Handles the reset password screen
?>

<footer id="colophon" class=" <?php echo is_checkout() ? 'shopchop-footer-minimal' : 'shopchop-footer-normal'; ?>">
<?php if ($is_minimal_page) : ?>

<?php else : ?>

<?php endif; ?>


	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<aside role="complementary" aria-label="<?php esc_attr_e( 'Footer', 'shopchop' ); ?>">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</aside>
	<?php endif; ?>

	<?php if ( has_nav_menu( 'menu-2' ) ) : ?>
		<nav aria-label="<?php esc_attr_e( 'Footer Menu', 'shopchop' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-2',
					'menu_class'     => 'footer-menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	<?php endif; ?>

	<div>
		&copy;
		<?php echo date("Y"); ?>
		<?php
		$shopchop_blog_info = get_bloginfo( 'name' );
		if ( ! empty( $shopchop_blog_info ) ) :
			?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>,
			<?php
		endif;

		/* translators: 1: WordPress link, 2: WordPress. */
		printf(
			'<a href="%1$s">proudly powered by %2$s</a>.',
			esc_url( __( 'https://wordpress.org/', 'shopchop' ) ),
			'WordPress'
		);
		?>
	</div>

</footer><!-- #colophon -->
