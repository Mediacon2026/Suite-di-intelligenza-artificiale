<?php
/**
 * Uniform editorial archive template.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var \WP_Query $query Editorial query.
 * @var \Mediacon\Enterprise\Modules\Mediation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$selected_search = filter_input( INPUT_GET, 'mediation_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_year   = filter_input( INPUT_GET, 'mediation_year', FILTER_VALIDATE_INT );
$current_year    = (int) gmdate( 'Y' );
$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-mediation">
	<section class="me-mediation__section" aria-labelledby="me-archive-title">
		<div class="me-mediation__container">
			<h2 id="me-archive-title" class="screen-reader-text"><?php echo esc_html( $content['title'] ); ?></h2>
			<form class="me-mediation__filters" method="get" action="<?php echo esc_url( get_permalink() ); ?>" role="search">
				<label>
					<span><?php echo esc_html__( 'Cerca', 'mediacon-enterprise' ); ?></span>
					<input type="search" name="mediation_search" value="<?php echo esc_attr( is_string( $selected_search ) ? $selected_search : '' ); ?>">
				</label>
				<label>
					<span><?php echo esc_html__( 'Anno', 'mediacon-enterprise' ); ?></span>
					<select name="mediation_year">
						<option value=""><?php echo esc_html__( 'Tutti gli anni', 'mediacon-enterprise' ); ?></option>
						<?php for ( $archive_year = $current_year; $archive_year >= $current_year - 8; $archive_year-- ) : ?>
							<option value="<?php echo esc_attr( (string) $archive_year ); ?>" <?php selected( $selected_year, $archive_year ); ?>><?php echo esc_html( (string) $archive_year ); ?></option>
						<?php endfor; ?>
					</select>
				</label>
				<button class="me-mediation__button" type="submit"><?php echo esc_html__( 'Filtra', 'mediacon-enterprise' ); ?></button>
			</form>

			<?php if ( $query->have_posts() ) : ?>
				<div class="me-mediation__editorial-grid">
					<?php while ( $query->have_posts() ) : ?>
						<?php $query->the_post(); ?>
						<article <?php post_class( 'me-mediation__editorial-card' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<a class="me-mediation__editorial-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
									<?php
									the_post_thumbnail(
										'medium_large',
										array(
											'loading' => 'lazy',
											'alt'     => '',
										)
									);
									?>
								</a>
							<?php else : ?>
								<div class="me-mediation__editorial-image me-mediation__editorial-image--empty" aria-hidden="true"></div>
							<?php endif; ?>
							<div class="me-mediation__editorial-body">
								<p class="me-mediation__meta"><?php echo esc_html( get_the_date() ); ?></p>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="me-mediation__excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
								<a class="me-mediation__text-link" href="<?php the_permalink(); ?>"><?php echo esc_html__( 'Continua', 'mediacon-enterprise' ); ?><span class="screen-reader-text">: <?php the_title(); ?></span></a>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<nav class="me-mediation__pagination" aria-label="<?php echo esc_attr__( 'Paginazione degli articoli', 'mediacon-enterprise' ); ?>">
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
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="me-mediation__empty" role="status">
					<p><?php echo esc_html__( 'Nessun contenuto corrisponde ai filtri selezionati.', 'mediacon-enterprise' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>
