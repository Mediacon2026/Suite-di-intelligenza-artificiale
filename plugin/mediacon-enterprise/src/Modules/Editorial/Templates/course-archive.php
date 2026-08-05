<?php
/**
 * Formation courses inside the editorial card framework.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;

$types = array(
	'base'     => __( 'Corso base', 'mediacon-enterprise' ),
	'advanced' => __( 'Approfondimento', 'mediacon-enterprise' ),
	'renewal'  => __( 'Aggiornamento', 'mediacon-enterprise' ),
	'event'    => __( 'Evento formativo', 'mediacon-enterprise' ),
	'seminar'  => __( 'Seminario', 'mediacon-enterprise' ),
	'workshop' => __( 'Workshop', 'mediacon-enterprise' ),
);
$components->renderComponent(
	'hero',
	array(
		'title'       => $archive['title'],
		'description' => __( 'Corsi e attività formative in programma pubblicati da Mediacon.', 'mediacon-enterprise' ),
	)
);
$components->renderComponent( 'breadcrumb', array( 'current' => $archive['title'] ) );
?>
<main id="main-content" class="me-editorial" style="--me-editorial-columns: <?php echo esc_attr( (string) $cards->general()['columns'] ); ?>">
	<section class="me-editorial__section"><div class="me-editorial__container">
		<?php
		if ( $query->have_posts() ) :
			?>
			<div class="me-editorial__grid">
			<?php
			while ( $query->have_posts() ) :
				?>
				<?php $query->the_post(); ?><?php $course = $course_repository->data( get_the_ID() ); ?>
				<?php
				$components->renderComponent(
					'course-card',
					array(
						'course'       => $course,
						'type_label'   => $types[ $course['type'] ] ?? __( 'Corso', 'mediacon-enterprise' ),
						'status_label' => $course_repository->statusLabel( $course['status'] ),
					)
				);
				?>
				<?php endwhile; ?>
		</div><?php wp_reset_postdata(); ?>
			<?php
		else :
			?>
					<div class="me-editorial__empty" role="status"><h2><?php esc_html_e( 'Nessun corso in programma', 'mediacon-enterprise' ); ?></h2><p><?php esc_html_e( 'Le nuove attività formative saranno pubblicate in questa sezione.', 'mediacon-enterprise' ); ?></p></div><?php endif; ?>
	</div></section>
	<?php
	$components->renderComponent(
		'cta',
		array(
			'title'       => __( 'Scopri la formazione Mediacon', 'mediacon-enterprise' ),
			'description' => __( 'Consulta percorsi, docenti e modalità di iscrizione.', 'mediacon-enterprise' ),
			'label'       => __( 'Formazione mediatori', 'mediacon-enterprise' ),
			'url'         => home_url( '/formazione-mediatori/' ),
		)
	);
	?>
</main>
