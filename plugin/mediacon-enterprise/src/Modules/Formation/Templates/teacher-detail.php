<?php
/**
 * Public teacher detail template.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $teacher Teacher data.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$components->renderComponent(
	'hero',
	array(
		'eyebrow' => __( 'Docenti Mediacon', 'mediacon-enterprise' ),
		'title'   => $teacher['title'],
		'intro'   => $teacher['qualification'],
	)
);
$components->renderComponent( 'breadcrumb', array( 'current' => $teacher['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section">
		<div class="me-formation__container me-formation__teacher-profile">
			<?php if ( $teacher['image'] ) : ?>
				<img class="me-formation__teacher-photo" src="<?php echo esc_url( $teacher['image'] ); ?>" alt="">
			<?php endif; ?>
			<div class="me-formation__prose">
				<?php if ( $teacher['biography'] ) : ?>
					<h2><?php esc_html_e( 'Biografia', 'mediacon-enterprise' ); ?></h2>
					<?php echo wp_kses_post( apply_filters( 'the_content', $teacher['biography'] ) ); ?>
				<?php endif; ?>
				<?php if ( $teacher['expertise'] ) : ?>
					<h2><?php esc_html_e( 'Competenze', 'mediacon-enterprise' ); ?></h2>
					<?php echo wp_kses_post( wpautop( $teacher['expertise'] ) ); ?>
				<?php endif; ?>
				<?php if ( $teacher['courses'] ) : ?>
					<h2><?php esc_html_e( 'Corsi e attività formative', 'mediacon-enterprise' ); ?></h2>
					<?php echo wp_kses_post( wpautop( $teacher['courses'] ) ); ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
