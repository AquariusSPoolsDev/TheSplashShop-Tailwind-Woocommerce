<?php
/**
 * Template part for displaying a message when posts are not found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ShopChop
 */

?>

<section>

	<header class="page-header">
		<?php if ( is_search() ) : ?>

			<?php
			printf(
				/* translators: 1: search result title. 2: search term. */
				'<h1 class="page-title">%1$s <span>%2$s</span></h1>',
				esc_html__( 'Search results for:', 'shopchop' ),
				esc_html( get_search_query() )
			);
			?>

		<?php else : ?>

			<h1 class="page-title nothing-found"><?php esc_html_e( 'Nothing found', 'shopchop' ); ?></h1>

		<?php endif; ?>
	</header><!-- .page-header -->

	<div <?php shopchop_content_class( 'page-content' ); ?>>
		<div class="content-none-body">
			<?php
			if ( is_home() && current_user_can( 'publish_posts' ) ) :
				?>

				<p>
					<?php esc_html_e( 'Your site is set to show the most recent posts on your homepage, but you haven&rsquo;t published any posts.', 'shopchop' ); ?>
				</p>

				<p>
					<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
						<?php
						/* translators: 1: link to WP admin new post page. */
						esc_html_e( 'Add or publish posts', 'shopchop' );
						?>
					</a>
				</p>

				<?php
			elseif ( is_search() ) :
				?>

				<p>
					<?php esc_html_e( 'Your search generated no results. Please try a different search.', 'shopchop' ); ?>
				</p>

				<?php
				get_search_form();
			else :
				?>

				<p>
					<?php esc_html_e( 'No content matched your request.', 'shopchop' ); ?>
				</p>

				<?php
				get_search_form();
			endif;
			?>
		</div>
		
	</div><!-- .page-content -->

</section>
