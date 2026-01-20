<?php
/**
 * Template part for displaying My Account Page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ShopChop
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(["shopchop-my-account"]); ?>>

<?php the_content(); ?>

</article><!-- #post-<?php the_ID(); ?> -->
