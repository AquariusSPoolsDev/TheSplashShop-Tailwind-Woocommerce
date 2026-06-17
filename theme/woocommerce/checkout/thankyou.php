<?php

/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;
?>
<div id="printable-receipt" class="wc-order-thank-you-page">
	<div class="woocommerce-order">

		<?php
		if ($order) :

			do_action('woocommerce_before_thankyou', $order->get_id());
		?>

			<?php if ($order->has_status('failed')) : ?>
				<div class="thank-you-title-wrapper">
					<div class="">
						<h2 class="woocommerce-order-received-title"><?php esc_html_e('Oops, We\'re Sorry!', 'woocommerce'); ?></h2>

						<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>

						<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
							<a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
							<?php if (is_user_logged_in()) : ?>
								<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('My account', 'woocommerce'); ?></a>
							<?php endif; ?>
						</p>
					</div>
					<?php
					$icon = get_stylesheet_directory_uri() . '/assets/images/order-failed.png';
					?>
					<img class="wc-order-icon" src="<?php echo esc_url($icon); ?>" alt="Order Failed">
				</div>


			<?php else : ?>
				<?php wc_get_template('checkout/order-received.php', array('order' => $order)); ?>

				<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

					<li class="woocommerce-order-overview__order order">
						<?php esc_html_e('Order number', 'woocommerce'); ?>
						<strong><?php echo esc_html( $order->get_order_number() ); ?></strong>
					</li>

					<li class="woocommerce-order-overview__date date">
						<?php esc_html_e('Date', 'woocommerce'); ?>
						<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
					</li>

					<li class="woocommerce-order-overview__total total">
						<?php esc_html_e('Total', 'woocommerce'); ?>
						<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
					</li>

					<?php if ($order->get_payment_method_title()) : ?>
						<li class="woocommerce-order-overview__payment-method method">
							<?php esc_html_e('Payment method', 'woocommerce'); ?>
							<strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
						</li>
					<?php endif; ?>

				</ul>

			<?php endif; ?>

			<?php /* do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); */ ?>

			<?php do_action('woocommerce_thankyou', $order->get_id()); ?>

		<?php else : ?>

			<?php wc_get_template('checkout/order-received.php', array('order' => false)); ?>

		<?php endif; ?>

	</div>

	<div class="wc-thank-you-button-actions">
		<a href="javascript:window.print()" class="button print-receipt">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true"><path d="M214.67,72H200V40a8,8,0,0,0-8-8H64a8,8,0,0,0-8,8V72H41.33C27.36,72,16,82.77,16,96v80a8,8,0,0,0,8,8H56v32a8,8,0,0,0,8,8H192a8,8,0,0,0,8-8V184h32a8,8,0,0,0,8-8V96C240,82.77,228.64,72,214.67,72ZM72,48H184V72H72ZM184,208H72V160H184Zm40-40H200V152a8,8,0,0,0-8-8H64a8,8,0,0,0-8,8v16H32V96c0-4.41,4.19-8,9.33-8H214.67c5.14,0,9.33,3.59,9.33,8Zm-24-52a12,12,0,1,1-12-12A12,12,0,0,1,200,116Z"></path></svg>
			<?php esc_html_e('Print Receipt', 'woocommerce'); ?>
		</a>
		<a href="<?php echo esc_url( home_url() ); ?>" class="button continue-shopping">
			<?php esc_html_e('Continue Shopping', 'woocommerce'); ?>
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true"><path d="M221.66,133.66l-72,72a8,8,0,0,1-11.32-11.32L196.69,136H40a8,8,0,0,1,0-16H196.69L138.34,61.66a8,8,0,0,1,11.32-11.32l72,72A8,8,0,0,1,221.66,133.66Z"></path></svg>
		</a>
	</div>
</div>