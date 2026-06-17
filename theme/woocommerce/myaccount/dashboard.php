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
		<div class="flex flex-grow">
			<div class="user-icon">
				<?php
				$current_user = wp_get_current_user();
				echo get_avatar($current_user->ID, 96);
				?>
			</div>
			<div class="user-details">
				<h2><?php echo esc_html( $current_user->display_name ); ?></h2>
				<hr>
				<p class="bg-teal-100 text-teal-800! border-l-2 border-teal-800 p-2 rounded-r-lg">Unlock exclusive rewards with your points. Stay tuned!</p>
			</div>
		</div>

		<div class="user-links">
			<a class="button user-logout" href="<?php echo esc_url(wc_logout_url()); ?>">Log Out</a>

			<?php if (current_user_can('manage_options')) : ?>
				<a target="_blank" rel="noopener noreferrer" class="admin-access" href="<?php echo esc_url( admin_url() ); ?>">Site Admin</a>
			<?php endif; ?>
		</div>

	</div>
</div>

<hr>

<div class="dashboard-user-actions">
	<a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" class="user-action-order">
		<div class="action-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="lucide lucide-package-icon lucide-package"><path d="M223.68,66.15,135.68,18a15.88,15.88,0,0,0-15.36,0l-88,48.17a16,16,0,0,0-8.32,14v95.64a16,16,0,0,0,8.32,14l88,48.17a15.88,15.88,0,0,0,15.36,0l88-48.17a16,16,0,0,0,8.32-14V80.18A16,16,0,0,0,223.68,66.15ZM128,32l80.34,44-29.77,16.3-80.35-44ZM128,120,47.66,76l33.9-18.56,80.34,44ZM40,90l80,43.78v85.79L40,175.82Zm176,85.78h0l-80,43.79V133.82l32-17.51V152a8,8,0,0,0,16,0V107.55L216,90v85.77Z"></path></svg>
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
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="lucide lucide-map-pinned-icon lucide-map-pinned"><path d="M112,80a16,16,0,1,1,16,16A16,16,0,0,1,112,80ZM64,80a64,64,0,0,1,128,0c0,59.95-57.58,93.54-60,94.95a8,8,0,0,1-7.94,0C121.58,173.54,64,140,64,80Zm16,0c0,42.2,35.84,70.21,48,78.5,12.15-8.28,48-36.3,48-78.5a48,48,0,0,0-96,0Zm122.77,67.63a8,8,0,0,0-5.54,15C213.74,168.74,224,176.92,224,184c0,13.36-36.52,32-96,32s-96-18.64-96-32c0-7.08,10.26-15.26,26.77-21.36a8,8,0,0,0-5.54-15C29.22,156.49,16,169.41,16,184c0,31.18,57.71,48,112,48s112-16.82,112-48C240,169.41,226.78,156.49,202.77,147.63Z"></path></svg>
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
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="lucide lucide-user-round-pen-icon lucide-user-round-pen"><path d="M227.32,73.37,182.63,28.69a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H216a8,8,0,0,0,0-16H115.32l112-112A16,16,0,0,0,227.32,73.37ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.69,147.32,64l24-24L216,84.69Z"></path></svg>
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
