<?php

/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

?>

<div class="dashboard-user">
	<div class="user-hero-card">
		<div class="user-icon">
			<?php
			$current_user = wp_get_current_user();
			echo get_avatar($current_user->ID, 96);
			?>
		</div>
		<div class="user-details">
			<h2>
				<?php
				printf(
					esc_html__('%s', 'woocommerce'),
					esc_html($current_user->display_name)
				);
				?>
			</h2>
			<hr>
			<p><strong>Points: </strong><span><?php echo '999'; ?></span> points.</p>
		</div>

		<div class="user-links">
			<a class="button user-logout" href="<?php echo esc_url(wc_logout_url()); ?>">Log Out</a>

			<?php if (current_user_can('manage_options')) : ?>
				<a target="_blank" rel="noopener noreferrer" class="admin-access" href="<?php echo admin_url(); ?>" class="admin-access-link">Site Admin</a>
			<?php endif; ?>
		</div>

	</div>
</div>

<hr>

<div class="dashboard-user-actions">
	<a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" class="user-action-order">
		<div class="action-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-icon lucide-package">
				<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" />
				<path d="M12 22V12" />
				<polyline points="3.29 7 12 12 20.71 7" />
				<path d="m7.5 4.27 9 5.15" />
			</svg>
		</div>
		<div class="action-title">
			<h3>
				<?php
				$title = __('Your Orders', 'woocommerce');
				echo esc_html($title);
				?>
			</h3>
			<p><?php echo esc_html__('View your orders in one page.', 'woocommerce'); ?></p>
		</div>
	</a>

	<a href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" class="user-action-billing">
		<div class="action-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pinned-icon lucide-map-pinned">
				<path d="M18 8c0 3.613-3.869 7.429-5.393 8.795a1 1 0 0 1-1.214 0C9.87 15.429 6 11.613 6 8a6 6 0 0 1 12 0" />
				<circle cx="12" cy="8" r="2" />
				<path d="M8.714 14h-3.71a1 1 0 0 0-.948.683l-2.004 6A1 1 0 0 0 3 22h18a1 1 0 0 0 .948-1.316l-2-6a1 1 0 0 0-.949-.684h-3.712" />
			</svg>
		</div>
		<div class="action-title">
			<h3>
				<?php
				$title = __('Billing Address', 'woocommerce');
				if (wc_shipping_enabled()) {
					$title = __('Addresses', 'woocommerce');
				}
				echo esc_html($title);
				?>
			</h3>
			<p><?php echo esc_html__('Edit / Update your billing and shipping addresses.', 'woocommerce'); ?></p>
		</div>
	</a>

	<a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>" class="user-action-account-edit">
		<div class="action-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-pen-icon lucide-user-round-pen">
				<path d="M2 21a8 8 0 0 1 10.821-7.487" />
				<path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" />
				<circle cx="10" cy="8" r="5" />
			</svg>
		</div>
		<div class="action-title">
			<h3>
				<?php
				$title = __('Account Details', 'woocommerce');
				echo esc_html($title);
				?>
			</h3>
			<p><?php echo esc_html__('Edit / Update your account details.', 'woocommerce'); ?></p>
		</div>
	</a>
</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action('woocommerce_account_dashboard');

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action('woocommerce_before_my_account');

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action('woocommerce_after_my_account');

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
