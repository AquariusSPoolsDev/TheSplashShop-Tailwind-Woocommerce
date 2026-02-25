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
			<div class="container mx-auto px-6 md:px-10 lg:px-16">
				<div class="header-normal-content flex flex-col">
					<div class="search-bar-row flex items-center w-full lg:gap-6 py-4 border-b border-b-grey-200">
						<div class="shopchop-logo-meta lg:shrink-0">
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

						<div class="shopchop-search-wrapper">
							<div class="shopchop-search-bar">
								<input type="text" id="shopchop-search-input" placeholder="Search Here..." autocomplete="off">
								<select id="shopchop-cat-select">
									<option value="all">All Products</option>
								</select>
							</div>
							<div class="shopchop-search-results" style="display: none;"></div>
						</div>

						<div class="shopchop-account-controls">

							<div class="shopchop-wishlist-wrapper">
								<a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="shopchop-wishlist-btn" aria-label="Wishlist">
									<svg class="wishlist-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
									</svg>
								</a>
							</div>

							<?php echo do_shortcode('[shopchop_mini_cart]'); ?>

							<?php $is_logged_in = is_user_logged_in(); ?>
							<div class="shopchop-account-wrapper">
								<button class="shopchop-account-trigger" aria-label="Account Menu" aria-expanded="false">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="user-icon">
										<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
										<circle cx="12" cy="7" r="4" />
									</svg>
									<span class="account-label">
										<?php
										if ($is_logged_in) {
											$current_user = wp_get_current_user();
											$user_name = $current_user->display_name;
											echo esc_html($user_name);
										} else {
											echo 'Log In / Register';
										}
										?>
									</span>
								</button>

								<div class="shopchop-account-dropdown" style="display: none;">
									<div class="account-dropdown-content">
										<?php if ($is_logged_in) : ?>
											<?php
											$current_user = wp_get_current_user();
											$user_name = $current_user->display_name;
											?>

											<!-- Logged In View -->
											<div class="account-header">
												<div class="user-greeting">
													<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
														<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
														<circle cx="12" cy="7" r="4"></circle>
													</svg>
													<span>Hello, <strong><?php echo esc_html($user_name); ?></strong></span>
												</div>
											</div>

											<div class="account-menu-items">
												<a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="account-menu-item">
													<span>My Account</span>
												</a>
												<a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="account-menu-item">
													<span>Orders</span>
												</a>
												<a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="account-menu-item">
													<span>Addresses</span>
												</a>
												<a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="account-menu-item">
													<span>Account Details</span>
												</a>
											</div>

											<div class="account-menu-footer">
												<a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="logout-link">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="log-out-icon">
														<path d="m16 17 5-5-5-5" />
														<path d="M21 12H9" />
														<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
													</svg>
													<span>Log Out</span>
												</a>
											</div>

										<?php else : ?>

											<!-- Not Logged In View -->
											<div class="account-login-prompt">
												<div class="login-icon">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="user-icon">
														<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
														<circle cx="12" cy="7" r="4" />
													</svg>
												</div>
												<h3>Sign in to <?php bloginfo('name'); ?></h3>
												<p>Access your account for order management and more</p>
												<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="login-button">
													Sign In
												</a>
												<div class="register-prompt">
													Don't have an account?
													<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Register</a>
												</div>
											</div>

										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="menu-bar-row">
						<nav id="site-navigation" aria-label="<?php esc_attr_e('Main Navigation', 'shopchop'); ?>">
							<button class="sr-only" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e('Primary Menu', 'shopchop'); ?></button>

							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'menu-1',
									'menu_id'        => 'primary-menu',
									'items_wrap'     => '<ul id="%1$s" class="%2$s flex gap-3" aria-label="submenu">%3$s</ul>',
								)
							);
							?>
						</nav><!-- #site-navigation -->
					</div>
				</div><!-- header-normal-content end -->
			<?php endif; ?>
			</div><!-- container end -->
</header><!-- #masthead -->