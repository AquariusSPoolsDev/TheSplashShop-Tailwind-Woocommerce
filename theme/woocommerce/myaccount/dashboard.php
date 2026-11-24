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
		<p class="greeting">
			<?php
			/* translators: %s: customer first name */
			printf( esc_html__( 'Hi, %s', 'shopchop' ), esc_html( $first_name ) );
			?>
		</p>
		<p class="signed-in-as"><?php echo esc_html( $current_user->user_email ); ?></p>
	</div>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<a target="_blank" rel="noopener noreferrer" class="admin-access" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Site admin', 'shopchop' ); ?></a>
	<?php endif; ?>
</div>

<div class="dashboard-grid">

	<section class="dashboard-card dashboard-latest-order" aria-labelledby="dashboard-order-title">
		<h2 id="dashboard-order-title"><?php esc_html_e( 'Latest order', 'shopchop' ); ?></h2>

		<?php if ( $latest_order ) : ?>
			<div class="card-row">
				<a class="order-number" href="<?php echo esc_url( $latest_order->get_view_order_url() ); ?>">
					<?php
					/* translators: %s: order number */
					printf( esc_html__( 'Order #%s', 'shopchop' ), esc_html( $latest_order->get_order_number() ) );
					?>
				</a>
				<span class="status-badge status-<?php echo esc_attr( $latest_order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $latest_order->get_status() ) ); ?></span>
			</div>

			<div class="order-thumbs">
				<?php
				foreach ( array_slice( $latest_order->get_items(), 0, 3 ) as $item ) {
					$product = $item->get_product();
					echo $product ? wp_kses_post( $product->get_image( array( 64, 64 ) ) ) : '';
				}
				?>
			</div>

			<p class="order-summary">
				<strong><?php echo wp_kses_post( $latest_order->get_formatted_order_total() ); ?></strong>
				<span>
					<?php
					$count = $latest_order->get_item_count();
					/* translators: 1: item count, 2: order date */
					printf( esc_html( _n( '%1$s item, ordered %2$s', '%1$s items, ordered %2$s', $count, 'shopchop' ) ), esc_html( number_format_i18n( $count ) ), esc_html( wc_format_datetime( $latest_order->get_date_created() ) ) );
					?>
				</span>
			</p>

			<div class="card-actions">
				<a class="button" href="<?php echo esc_url( $latest_order->get_view_order_url() ); ?>"><?php esc_html_e( 'View order', 'shopchop' ); ?></a>
				<a class="card-link" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'All orders', 'shopchop' ); ?></a>
			</div>
		<?php else : ?>
			<p class="card-empty"><?php esc_html_e( "You haven't placed an order yet.", 'shopchop' ); ?></p>
			<div class="card-actions">
				<a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse products', 'shopchop' ); ?></a>
			</div>
		<?php endif; ?>
	</section>

	<?php if ( function_exists( 'shopchop_get_pool_profiles' ) ) : ?>
		<section class="dashboard-card dashboard-pool" aria-labelledby="dashboard-pool-title">
			<h2 id="dashboard-pool-title"><?php esc_html_e( 'Your pool', 'shopchop' ); ?></h2>

			<?php if ( $pool ) : ?>
				<?php
				$volume = (int) get_post_meta( $pool->ID, '_pool_volume', true );
				$type   = get_post_meta( $pool->ID, '_pool_type', true );
				?>
				<p class="pool-name"><?php echo esc_html( $pool->post_title ); ?></p>
				<ul class="pool-facts">
					<?php if ( $volume ) : ?>
						<li>
							<?php
							/* translators: %s: volume in litres */
							printf( esc_html__( '%s litres', 'shopchop' ), esc_html( number_format_i18n( $volume ) ) );
							?>
						</li>
					<?php endif; ?>
					<?php if ( $type ) : ?>
						<li><?php echo esc_html( SHOPCHOP_POOL_TYPES[ $type ] ?? $type ); ?></li>
					<?php endif; ?>
				</ul>
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
