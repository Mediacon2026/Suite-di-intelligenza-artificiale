<?php
/**
 * Formation insights archive.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var \WP_Query $query Insight query.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$formation_search = filter_input( INPUT_GET, 'formation_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section">
		<div class="me-formation__container">
			<form class="me-formation__filters" method="get" action="<?php echo esc_url( get_permalink() ); ?>" role="search"><label><span><?php echo esc_html__( 'Cerca negli approfondimenti', 'mediacon-enterprise' ); ?></span><input type="search" name="formation_search" value="<?php echo esc_attr( is_string( $formation_search ) ? $formation_search : '' ); ?>"></label><button class="me-formation__button" type="submit"><?php echo esc_html__( 'Cerca', 'mediacon-enterprise' ); ?></button></form>
			<?php
			if ( $query->have_posts() ) :
				?>
				<div class="me-formation__insight-grid">
				<?php
				while ( $query->have_posts() ) :
					?>
					<?php $query->the_post(); ?><article class="me-formation__insight-card">
					<?php
					if ( has_post_thumbnail() ) :
						?>
					<a class="me-formation__course-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php
						the_post_thumbnail(
							'medium_large',
							array(
								'alt'     => '',
								'loading' => 'lazy',
							)
						);
						?>
					</a>
						<?php
else :
	?>
	<div class="me-formation__course-image me-formation__course-image--empty" aria-hidden="true"></div><?php endif; ?><div class="me-formation__course-body"><p class="me-formation__meta"><?php echo esc_html( get_the_date() ); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p class="me-formation__clamp"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p><a class="me-formation__text-link" href="<?php the_permalink(); ?>"><?php echo esc_html__( 'Continua', 'mediacon-enterprise' ); ?><span class="screen-reader-text">: <?php the_title(); ?></span></a></div></article><?php endwhile; ?>
			</div>
				<?php if ( $query->max_num_pages > 1 ) : ?>
				<nav class="me-formation__pagination" aria-label="<?php echo esc_attr__( 'Paginazione degli approfondimenti', 'mediacon-enterprise' ); ?>">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'total'   => $query->max_num_pages,
								'current' => max( 1, (int) get_query_var( 'paged', 1 ) ),
								'type'    => 'list',
							)
						)
					);
					?>
																	</nav>
			<?php endif; ?>
				<?php wp_reset_postdata(); ?>
				<?php
			else :
				?>
				<div class="me-formation__empty" role="status"><p><?php echo esc_html__( 'Nessun approfondimento corrisponde alla ricerca.', 'mediacon-enterprise' ); ?></p></div><?php endif; ?>
		</div>
	</section>
</main>
