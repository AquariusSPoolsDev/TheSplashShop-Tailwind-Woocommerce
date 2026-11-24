<?php
/**
 * My Account Dashboard
 *
 * ShopChop: a status page, not a second menu. Shows the latest order, the
 * customer's pool profile and their delivery address; the account menu
 * covers everything else.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$first_name   = $current_user->first_name ? $current_user->first_name : $current_user->display_name;

$latest_order = wc_get_orders(
	array(
		'customer_id' => get_current_user_id(),
		'limit'       => 1,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'status'      => array_keys( wc_get_order_statuses() ),
	)
);
$latest_order = $latest_order ? $latest_order[0] : null;

// The newest order not yet dispatched; when there is none, the card shows the latest order instead.
$active_orders = wc_get_orders(
	array(
		'customer_id' => get_current_user_id(),
		'status'      => array( 'pending', 'on-hold', 'processing' ),
		'limit'       => 1,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
$card_orders = $active_orders ? $active_orders : ( $latest_order ? array( $latest_order ) : array() );

$buy_again = function_exists( 'shopchop_buy_again_products' ) ? shopchop_buy_again_products( get_current_user_id(), 4 ) : array();

$pools = function_exists( 'shopchop_get_pool_profiles' ) ? shopchop_get_pool_profiles() : array();
$pool  = $pools ? $pools[0] : null;

$address_type = wc_shipping_enabled() ? 'shipping' : 'billing';
$address      = wc_get_account_formatted_address( $address_type );
if ( ! $address && 'shipping' === $address_type ) {
	$address_type = 'billing';
	$address      = wc_get_account_formatted_address( 'billing' );
}
?>

<div class="dashboard-welcome">
	<div class="user-icon"><?php echo get_avatar( $current_user->ID, 64 ); ?></div>
	<div class="user-details">
		<h1 class="greeting">
			<?php
			/* translators: %s: customer first name */
			printf( esc_html__( 'Hi, %s', 'shopchop' ), esc_html( $first_name ) );
			?>
		</h1>
		<p class="signed-in-as"><?php echo esc_html( $current_user->user_email ); ?></p>
	</div>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<a target="_blank" rel="noopener noreferrer" class="admin-access" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Site admin', 'shopchop' ); ?></a>
	<?php endif; ?>
</div>

<div class="dashboard-grid">

	<section class="dashboard-card dashboard-orders" aria-labelledby="dashboard-order-title">
		<div class="card-head">
			<h2 id="dashboard-order-title">
				<?php echo $active_orders ? esc_html__( 'In progress', 'shopchop' ) : esc_html__( 'Latest order', 'shopchop' ); ?>
			</h2>
			<?php if ( $card_orders ) : ?>
				<a class="all-orders" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'All orders', 'shopchop' ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( $card_orders ) : ?>
			<?php foreach ( $card_orders as $card_order ) : ?>
				<?php $progress = shopchop_order_progress_html( $card_order ); ?>
				<article class="dashboard-order">
					<div class="card-row">
						<a class="order-number" href="<?php echo esc_url( $card_order->get_view_order_url() ); ?>">
							<?php
							/* translators: %s: order number */
							printf( esc_html__( 'Order #%s', 'shopchop' ), esc_html( $card_order->get_order_number() ) );
							?>
						</a>
						<?php if ( ! $progress ) : // the tracker already shows the status ?>
							<span class="status-badge status-<?php echo esc_attr( $card_order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $card_order->get_status() ) ); ?></span>
						<?php endif; ?>
					</div>

					<?php echo $progress; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?>

					<div class="order-body">
						<div class="order-thumbs">
							<?php
							foreach ( array_slice( $card_order->get_items(), 0, 3 ) as $item ) {
								$product = $item->get_product();
								echo $product ? wp_kses_post( $product->get_image( array( 64, 64 ) ) ) : '';
							}
							?>
						</div>
						<p class="order-summary">
							<strong><?php echo wp_kses_post( $card_order->get_formatted_order_total() ); ?></strong>
							<span>
								<?php
								$count = $card_order->get_item_count();
								/* translators: 1: item count, 2: order date */
								printf( esc_html( _n( '%1$s item, ordered %2$s', '%1$s items, ordered %2$s', $count, 'shopchop' ) ), esc_html( number_format_i18n( $count ) ), esc_html( wc_format_datetime( $card_order->get_date_created() ) ) );
								?>
							</span>
						</p>
					</div>

					<div class="card-actions">
						<a class="button button-outline" href="<?php echo esc_url( $card_order->get_view_order_url() ); ?>">
							<?php esc_html_e( 'View order', 'shopchop' ); ?><span class="sr-only"> #<?php echo esc_html( $card_order->get_order_number() ); ?></span>
						</a>
						<?php echo shopchop_order_help_html( $card_order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?>
					</div>
				</article>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="card-empty"><?php esc_html_e( "You haven't placed an order yet.", 'shopchop' ); ?></p>
			<div class="card-actions">
				<a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse products', 'shopchop' ); ?></a>
			</div>
		<?php endif; ?>
	</section>

	<?php if ( $buy_again ) : ?>
		<section class="dashboard-card dashboard-buy-again" aria-labelledby="dashboard-buy-again-title">
			<h2 id="dashboard-buy-again-title"><?php esc_html_e( 'Buy again', 'shopchop' ); ?></h2>
			<ul class="buy-again-list">
				<?php foreach ( $buy_again as $product ) : ?>
					<?php
					$name = $product->get_name();
					// A variation saved with an "any" attribute can't go straight to the cart.
					$needs_choice = $product->is_type( 'variation' ) && in_array( '', $product->get_variation_attributes(), true );
					$direct       = ! $needs_choice && ( $product->is_type( 'simple' ) || $product->is_type( 'variation' ) );
					?>
					<li class="buy-again-item">
						<a class="buy-again-image" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true"><?php echo wp_kses_post( $product->get_image( array( 96, 96 ) ) ); ?></a>
						<div class="buy-again-details">
							<a class="buy-again-name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $name ); ?></a>
							<span class="buy-again-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
						</div>
						<?php if ( $direct ) : ?>
							<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>" class="button add_to_cart_button ajax_add_to_cart" rel="nofollow" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Add to cart: %s', 'shopchop' ), $name ) ); ?>"><?php esc_html_e( 'Add to cart', 'shopchop' ); ?></a>
						<?php else : ?>
							<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="button button-outline" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Choose options: %s', 'shopchop' ), $name ) ); ?>"><?php esc_html_e( 'Choose options', 'shopchop' ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php if ( function_exists( 'shopchop_get_pool_profiles' ) ) : ?>
		<section class="dashboard-card dashboard-pool" aria-labelledby="dashboard-pool-title">
			<h2 id="dashboard-pool-title"><?php esc_html_e( 'Your pool', 'shopchop' ); ?></h2>

			<?php if ( $pool ) : ?>
				<p class="pool-name"><?php echo esc_html( $pool->post_title ); ?></p>
				<?php echo shopchop_pool_profile_facts_html( $pool->ID, array( 'volume', 'type', 'sanitiser', 'service' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?>
				<?php if ( count( $pools ) > 1 ) : ?>
					<p class="card-note">
						<?php
						$others = count( $pools ) - 1;
						/* translators: %s: number of other saved pools */
						printf( esc_html( _n( 'And %s more saved pool', 'And %s more saved pools', $others, 'shopchop' ) ), esc_html( number_format_i18n( $others ) ) );
						?>
					</p>
				<?php endif; ?>
				<div class="card-actions">
					<a class="button button-outline" href="<?php echo esc_url( wc_get_endpoint_url( 'pool-profile' ) ); ?>"><?php esc_html_e( 'Manage pools', 'shopchop' ); ?></a>
				</div>
			<?php else : ?>
				<p class="card-empty"><?php esc_html_e( "Save your pool's size, type and equipment in one place.", 'shopchop' ); ?></p>
				<div class="card-actions">
					<a class="button button-outline" href="<?php echo esc_url( add_query_arg( 'pool_action', 'add', wc_get_endpoint_url( 'pool-profile' ) ) ); ?>"><?php esc_html_e( 'Add your pool', 'shopchop' ); ?></a>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<section class="dashboard-card dashboard-address" aria-labelledby="dashboard-address-title">
		<h2 id="dashboard-address-title">
			<?php echo 'shipping' === $address_type ? esc_html__( 'Delivery address', 'shopchop' ) : esc_html__( 'Billing address', 'shopchop' ); ?>
		</h2>

		<?php if ( $address ) : ?>
			<address><?php echo wp_kses_post( $address ); ?></address>
			<div class="card-actions">
				<a class="button button-outline" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $address_type ) ); ?>">
					<?php esc_html_e( 'Edit', 'shopchop' ); ?><span class="sr-only"> <?php echo 'shipping' === $address_type ? esc_html__( 'delivery address', 'shopchop' ) : esc_html__( 'billing address', 'shopchop' ); ?></span>
				</a>
			</div>
		<?php else : ?>
			<p class="card-empty"><?php esc_html_e( 'No address saved yet. Add one to check out faster.', 'shopchop' ); ?></p>
			<div class="card-actions">
				<a class="button button-outline" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $address_type ) ); ?>"><?php esc_html_e( 'Add address', 'shopchop' ); ?></a>
			</div>
		<?php endif; ?>
	</section>

</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
