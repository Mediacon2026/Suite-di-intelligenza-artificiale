<?php
/**
 * Search results template with optional Enterprise component.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main section">
	<div class="container">
		<header class="archive-header"><p class="eyebrow"><?php esc_html_e( 'Ricerca', 'mediacon-one' ); ?></p><h1><?php esc_html_e( 'Risultati per:', 'mediacon-one' ); ?> <span><?php echo esc_html( get_search_query() ); ?></span></h1></header>
		<div class="search-page__form"><?php mediacon_one_render_search(); ?></div>
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
	get_template_part( 'template-parts/alert', null, array( 'message' => __( 'Nessun risultato. Prova con termini diversi.', 'mediacon-one' ) ) );
endif;
?>
	</div>
</main>
<?php get_footer(); ?>
