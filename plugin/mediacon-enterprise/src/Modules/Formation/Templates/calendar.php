<?php
/**
 * Accessible responsive course calendar.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var \WP_Query $query Course query.
 * @var \Mediacon\Enterprise\Modules\Formation\Services\CourseRepository $repository Course repository.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section">
		<div class="me-formation__container">
			<div class="me-formation__calendar" role="region" aria-label="<?php echo esc_attr__( 'Calendario dei prossimi corsi', 'mediacon-enterprise' ); ?>" tabindex="0">
				<table><thead><tr><th scope="col"><?php echo esc_html__( 'Data', 'mediacon-enterprise' ); ?></th><th scope="col"><?php echo esc_html__( 'Corso', 'mediacon-enterprise' ); ?></th><th scope="col"><?php echo esc_html__( 'Modalità', 'mediacon-enterprise' ); ?></th><th scope="col"><?php echo esc_html__( 'Stato', 'mediacon-enterprise' ); ?></th><th scope="col"><span class="screen-reader-text"><?php echo esc_html__( 'Dettagli', 'mediacon-enterprise' ); ?></span></th></tr></thead>
				<tbody>
					<?php
					while ( $query->have_posts() ) :
						?>
						<?php
						$query->the_post();
						$course = $repository->data( get_the_ID() );
						?>
<tr><td data-label="<?php echo esc_attr__( 'Data', 'mediacon-enterprise' ); ?>"><?php echo esc_html( $course['date_label'] ); ?></td><th scope="row" data-label="<?php echo esc_attr__( 'Corso', 'mediacon-enterprise' ); ?>"><?php echo esc_html( $course['title'] ); ?></th><td data-label="<?php echo esc_attr__( 'Modalità', 'mediacon-enterprise' ); ?>"><?php echo esc_html( ucfirst( $course['mode'] ) ); ?></td><td data-label="<?php echo esc_attr__( 'Stato', 'mediacon-enterprise' ); ?>"><?php echo esc_html( $repository->statusLabel( $course['status'] ) ); ?></td><td><a class="me-formation__text-link" href="<?php echo esc_url( $course['url'] ); ?>"><?php echo esc_html__( 'Dettagli', 'mediacon-enterprise' ); ?></a></td></tr><?php endwhile; ?>
				</tbody></table>
			</div>
			<?php
			if ( ! $query->have_posts() && 0 === $query->post_count ) :
				?>
				<div class="me-formation__empty" role="status"><p><?php echo esc_html__( 'Nessun corso futuro è attualmente pubblicato.', 'mediacon-enterprise' ); ?></p></div><?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</section>
</main>
