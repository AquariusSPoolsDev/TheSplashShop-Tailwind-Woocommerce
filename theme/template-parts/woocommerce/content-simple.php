<?php

/**
 * Template part for displaying Simple Layout Pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ShopChop
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(["shopchop-simple-layout"]); ?>>

    <header class="entry-header">
        <?php
        if (! is_wc_endpoint_url('order-received')) {
            if (! is_front_page()) {
                the_title('<h1 class="entry-title">', '</h1>');
            } else {
                the_title('<h2 class="entry-title">', '</h2>');
            }
        }
        ?>
    </header><!-- .entry-header -->

    <?php the_content(); ?>

</article><!-- #post-<?php the_ID(); ?> -->