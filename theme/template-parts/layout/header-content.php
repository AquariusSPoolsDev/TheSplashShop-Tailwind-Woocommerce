<?php

/**
 * Template part for displaying the header content
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

<header id="masthead" class="border-b border-b-grey-200 shadow-sm bg-background <?php echo is_checkout() ? 'shopchop-header-minimal' : 'shopchop-header-normal'; ?>">
	<?php if ($is_minimal_page) : ?>
		<div class="container mx-auto px-6 md:px-10 lg:px-16 py-4">
			<div class="header-minimal-content">
				<div class="shopchop-logo-meta">
					<?php
					if (is_front_page()) :
					?>
						<h1><?php bloginfo('name'); ?></h1>
					<?php
					else :
					?>
						<p><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
					<?php
					endif;

					$shopchop_description = get_bloginfo('description', 'display');
					if ($shopchop_description || is_customize_preview()) :
					?>
						<p><?php echo $shopchop_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?></p>
					<?php endif; ?>
				</div>
			</div><!-- header-minimal-content end -->

		<?php else : ?>
			<?php do_action('shopchop_demo_store_wrapper'); ?>
			<div class="container mx-auto px-6 md:px-10 lg:px-16 py-4">
				<div class="header-normal-content">
					<div class="shopchop-logo-meta">
						<?php
						if (is_front_page()) :
						?>
							<h1><?php bloginfo('name'); ?></h1>
						<?php
						else :
						?>
							<p><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
						<?php
						endif;

						$shopchop_description = get_bloginfo('description', 'display');
						if ($shopchop_description || is_customize_preview()) :
						?>
							<p><?php echo $shopchop_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
								?></p>
						<?php endif; ?>
					</div>

					<nav id="site-navigation" aria-label="<?php esc_attr_e('Main Navigation', 'shopchop'); ?>">
						<button aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e('Primary Menu', 'shopchop'); ?></button>

						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'menu_id'        => 'primary-menu',
								'items_wrap'     => '<ul id="%1$s" class="%2$s" aria-label="submenu">%3$s</ul>',
							)
						);
						?>
					</nav><!-- #site-navigation -->
				</div><!-- header-normal-content end -->

			<?php endif; ?>

			</div><!-- container end -->
</header><!-- #masthead -->