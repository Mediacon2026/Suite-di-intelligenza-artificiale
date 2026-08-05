<?php
/**
 * Public course detail template.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var array<string,mixed> $course Course data.
 * @var array<string,array<string,mixed>> $catalog Page catalog.
 * @var \Mediacon\Enterprise\Modules\Formation\Services\CourseRepository $repository Course repository.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$course_title     = '' !== $course['title'] ? $course['title'] : $content['title'];
$course_intro     = '' !== $course['excerpt'] ? $course['excerpt'] : $content['intro'];
$registration_url = '' !== $course['enroll_url'] ? $course['enroll_url'] : ( ! empty( $catalog['registration']['url'] ) ? $catalog['registration']['url'] : home_url( '/iscrizioni-formazione/' ) );
$audience         = '' !== $course['audience'] ? $course['audience'] : ( $content['audience'] ?? '' );
$requirements     = '' !== $course['requirements'] ? $course['requirements'] : ( $content['requirements'] ?? '' );
$hero             = array(
	'eyebrow' => $content['eyebrow'] ?? __( 'Corso Mediacon', 'mediacon-enterprise' ),
	'title'   => $course_title,
	'intro'   => $course_intro,
);
$components->renderComponent( 'hero', $hero );
$components->renderComponent( 'breadcrumb', array( 'current' => $hero['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section" aria-labelledby="me-course-info-title">
		<div class="me-formation__container">
			<div class="me-formation__course-heading">
				<h2 id="me-course-info-title"><?php echo esc_html__( 'Informazioni sul corso', 'mediacon-enterprise' ); ?></h2>
				<?php
				if ( $course['status'] ) :
					?>
					<?php
					$components->renderComponent(
						'badge',
						array(
							'status' => $course['status'],
							'label'  => $repository->statusLabel( $course['status'] ),
						)
					);
					?>
					<?php endif; ?>
			</div>
			<dl class="me-formation__facts">
				<?php
				foreach ( array(
					'date_label'   => __( 'Data', 'mediacon-enterprise' ),
					'duration'     => __( 'Durata', 'mediacon-enterprise' ),
					'mode'         => __( 'Modalità', 'mediacon-enterprise' ),
					'venue'        => __( 'Sede', 'mediacon-enterprise' ),
					'teachers'     => __( 'Docenti', 'mediacon-enterprise' ),
					'availability' => __( 'Disponibilità', 'mediacon-enterprise' ),
					'price'        => __( 'Quota', 'mediacon-enterprise' ),
				) as $field => $label ) :
					?>
					<?php
					if ( $course[ $field ] ) :
						?>
						<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $course[ $field ] ); ?></dd></div><?php endif; ?>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>
	<?php if ( $course['program'] ) : ?>
		<section class="me-formation__section me-formation__section--muted" aria-labelledby="me-program-title">
			<div class="me-formation__container me-formation__measure"><h2 id="me-program-title"><?php echo esc_html__( 'Programma', 'mediacon-enterprise' ); ?></h2><ol class="me-formation__program">
			<?php
			foreach ( $course['program'] as $item ) :
				?>
				<li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ol></div>
		</section>
	<?php endif; ?>
	<section class="me-formation__section">
		<div class="me-formation__container me-formation__two-columns">
			<article><h2><?php echo esc_html__( 'Destinatari', 'mediacon-enterprise' ); ?></h2><p><?php echo esc_html( $audience ); ?></p></article>
			<article><h2><?php echo esc_html__( 'Requisiti di accesso', 'mediacon-enterprise' ); ?></h2><p><?php echo esc_html( $requirements ); ?></p></article>
		</div>
	</section>
	<?php
	if ( have_posts() ) :
		?>
		<section class="me-formation__section me-formation__section--muted"><div class="me-formation__container me-formation__measure me-formation__prose">
		<?php
		while ( have_posts() ) {
				the_post();
				the_content(); }
		?>
</div></section><?php endif; ?>
	<?php
	$components->renderComponent(
		'cta',
		array(
			'title'       => __( 'Richiedi l’iscrizione', 'mediacon-enterprise' ),
			'description' => __( 'Consulta le condizioni pubblicate e invia la richiesta attraverso il canale indicato.', 'mediacon-enterprise' ),
			'label'       => __( 'Vai all’iscrizione', 'mediacon-enterprise' ),
			'url'         => $registration_url,
		)
	);
	?>
</main>
