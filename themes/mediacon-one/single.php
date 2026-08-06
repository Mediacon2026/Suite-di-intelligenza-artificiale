<?php
/**
 * Single post template.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<article <?php post_class( 'single-entry section' ); ?>><div class="container container--reading"><header class="single-entry__header"><p class="eyebrow"><?php the_category( ', ' ); ?></p><h1><?php the_title(); ?></h1><div class="entry-meta"><?php mediacon_one_posted_on(); ?></div></header>
		<?php
		if ( has_post_thumbnail() ) :
			?>
			<figure class="single-entry__media"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?></figure><?php endif; ?><div class="entry-content">
			<?php
			mediacon_one_the_content();
			wp_link_pages();
			?>
</div><?php the_post_navigation(); ?></div></article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
