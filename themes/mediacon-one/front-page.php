<?php
/**
 * Front page presentation using existing WordPress content.
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
	<section class="home-hero">
		<div class="container home-hero__inner">
			<div class="home-hero__copy">
				<p class="eyebrow"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php
				if ( has_excerpt() ) :
					?>
					<p class="home-hero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
				<div class="button-group"><a class="button" href="<?php echo esc_url( mediacon_one_page_url( array( 'mediazione', 'organismo-di-mediazione' ) ) ); ?>"><?php esc_html_e( 'Scopri la mediazione', 'mediacon-one' ); ?></a><a class="button button--light" href="<?php echo esc_url( mediacon_one_page_url( array( 'formazione', 'corsi' ) ) ); ?>"><?php esc_html_e( 'Esplora la formazione', 'mediacon-one' ); ?></a></div>
			</div>
		</div>
	</section>
	<section class="section"><div class="container entry-content entry-content--home"><?php mediacon_one_the_content(); ?></div></section>
	<?php endwhile; ?>
	<?php
	$service_pages = array(
		'mediazione' => __( 'Mediazione', 'mediacon-one' ),
		'formazione' => __( 'Formazione', 'mediacon-one' ),
		'preventivo' => __( 'Preventivo', 'mediacon-one' ),
	);
	$cards         = array();
	foreach ( $service_pages as $slug => $label ) {
		$service_page = get_page_by_path( $slug );
		if ( $service_page instanceof WP_Post ) {
			$cards[] = array(
				'page'  => $service_page,
				'label' => $label,
			);
		}
	}
	if ( $cards ) :
		?>
	<section class="section section--surface"><div class="container"><div class="card-grid card-grid--three">
		<?php foreach ( $cards as $card ) : ?>
		<article class="service-card"><span class="badge"><?php echo esc_html( $card['label'] ); ?></span><h2><a href="<?php echo esc_url( get_permalink( $card['page'] ) ); ?>"><?php echo esc_html( get_the_title( $card['page'] ) ); ?></a></h2><p><?php echo esc_html( wp_trim_words( get_the_excerpt( $card['page'] ), 24 ) ); ?></p></article>
	<?php endforeach; ?>
	</div></div></section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
