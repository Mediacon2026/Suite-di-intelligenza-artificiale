<?php
/**
 * Default index template.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main section">
	<div class="container">
		<header class="archive-header"><p class="eyebrow"><?php esc_html_e( 'Mediacon', 'mediacon-one' ); ?></p><h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1></header>
		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card' );
endwhile;
			?>
			</div>
			<?php mediacon_one_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/alert', null, array( 'message' => __( 'Nessun contenuto disponibile.', 'mediacon-one' ) ) ); ?>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
