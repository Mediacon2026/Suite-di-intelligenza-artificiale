<?php
/**
 * Archive template for blog, jurisprudence, legislation, insights, and courses.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main section">
	<div class="container">
		<header class="archive-header"><p class="eyebrow"><?php esc_html_e( 'Archivio', 'mediacon-one' ); ?></p><?php the_archive_title( '<h1>', '</h1>' ); ?><?php the_archive_description( '<div class="archive-description">', '</div>' ); ?></header>
		<?php
		if ( have_posts() ) :
			?>
			<div class="card-grid">
			<?php
			while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/card' );
endwhile;
			?>
</div><?php mediacon_one_pagination(); ?>
			<?php
else :
	get_template_part( 'template-parts/alert', null, array( 'message' => __( 'Nessun contenuto disponibile in questo archivio.', 'mediacon-one' ) ) );
endif;
?>
	</div>
</main>
<?php get_footer(); ?>
