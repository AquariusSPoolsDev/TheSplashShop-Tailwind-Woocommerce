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
		<div class="text-center text-sm container mx-auto px-6 md:px-10 lg:px-16 pt-4 p-8">
			&copy; <?php echo date("Y"); ?> <a class="underline! transition-all hover:no-underline! active:no-underline! focus:no-underline! text-primary-400 font-semibold" href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>. All Rights Reserved.<br>
			Website developed by <a class="underline! transition-all hover:no-underline! active:no-underline! focus:no-underline! text-primary-400 font-semibold" href="#">ChopChop</a>.
		</div>
	<?php else : ?>
		<div class=" container mx-auto px-6 md:px-10 lg:px-16 pt-4 p-8">
			<div class="">
				<?php if (is_active_sidebar('sidebar-1')) : ?>
					<aside role="complementary" aria-label="<?php esc_attr_e('Footer', 'shopchop'); ?>">
						<?php dynamic_sidebar('sidebar-1'); ?>
					</aside>
				<?php endif; ?>

				<?php if (has_nav_menu('menu-2')) : ?>
					<nav aria-label="<?php esc_attr_e('Footer Menu', 'shopchop'); ?>">
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
			</div>
			<div class="border-t border-t-grey-200">

			</div>
		</div>
	<?php endif; ?>




</footer><!-- #colophon -->