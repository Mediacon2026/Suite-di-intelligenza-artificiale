<?php
/**
 * Standard page template.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
get_header();
$context = mediacon_one_page_context();
?>
<main id="main-content" class="site-main page-layout page-layout--<?php echo esc_attr( $context[0] ); ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php
		get_template_part(
			'template-parts/hero',
			null,
			array(
				'eyebrow' => $context[1],
				'title'   => get_the_title(),
			)
		);
		?>
		<section class="section"><div class="container content-with-sidebar"><article class="entry-content<?php echo 'adhesion' === mediacon_one_current_page_key() ? ' entry-content--documents' : ''; ?>">
		<?php
		mediacon_one_the_content();
		wp_link_pages();
		?>
		</article><?php get_template_part( 'template-parts/sidebar' ); ?></div></section>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
