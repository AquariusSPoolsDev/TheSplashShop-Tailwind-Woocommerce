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

<header id="masthead" class="<?php echo is_checkout() ? 'shopchop-header-minimal' : 'shopchop-header-normal'; ?>">
	<?php if ($is_minimal_page) : ?>
		<div class="container mx-auto px-6 md:px-10 lg:px-16 py-4">
			<div class="header-minimal-content">
				<div class="shopchop-logo-meta">
					<?php
					if (has_custom_logo()) {
						$custom_logo_id = get_theme_mod('custom_logo');
						$logo = wp_get_attachment_image_src($custom_logo_id, 'full');
						$logo_src = $logo[0];
					} else {
						$logo_src = get_stylesheet_directory_uri() . '/assets/images/logo.png';
					}
					?>

					<a href="<?php echo esc_url(home_url('/')); ?>" class="block">
						<img
							src="<?php echo esc_url($logo_src); ?>"
							alt="<?php bloginfo('name'); ?>"
							title="<?php bloginfo('name'); ?>"
							class="h-16 w-auto">
					</a>
				</div>
				<div class="shopchop-tagline-desc">
					<?php

					$shopchop_description = get_bloginfo('description', 'display');
					if ($shopchop_description || is_customize_preview()) :
					?>
						<p class="text-sm italic text-end"><?php echo $shopchop_description; ?></p>
					<?php endif; ?>
				</div>
			</div><!-- header-minimal-content end -->

		<?php else : ?>
			<?php do_action('shopchop_demo_store_wrapper'); ?>
			<div class="container mx-auto px-6 md:px-10 lg:px-16 py-4">
				<div class="header-normal-content">
					<div class="search-bar-row">
						<div class="shopchop-logo-meta">
							<?php
							if (has_custom_logo()) {
								$custom_logo_id = get_theme_mod('custom_logo');
								$logo = wp_get_attachment_image_src($custom_logo_id, 'full');
								$logo_src = $logo[0];
							} else {
								$logo_src = get_stylesheet_directory_uri() . '/assets/images/logo.png';
							}
							?>

							<a href="<?php echo esc_url(home_url('/')); ?>" class="block">
								<img
									src="<?php echo esc_url($logo_src); ?>"
									alt="<?php bloginfo('name'); ?>"
									title="<?php bloginfo('name'); ?>"
									class="h-20 w-auto">
							</a>
						</div>
						<div class="shopchop-search-bar">

						</div>
						<div class="shopchop-account-controls">
							
						</div>
					</div>
					<div class="menu-bar-row">
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
					</div>
				</div><!-- header-normal-content end -->
			<?php endif; ?>
			</div><!-- container end -->
</header><!-- #masthead -->