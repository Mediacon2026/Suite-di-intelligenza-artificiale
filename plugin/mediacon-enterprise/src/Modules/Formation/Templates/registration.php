<?php
/**
 * Public registration information template.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var array<string,array<string,mixed>> $catalog Page catalog.
 * @var \Mediacon\Enterprise\Modules\Formation\Services\CourseRepository $repository Course repository.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$general = $repository->general();
$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section">
		<div class="me-formation__container me-formation__measure me-formation__prose">
			<h2><?php echo esc_html__( 'Come inviare la richiesta', 'mediacon-enterprise' ); ?></h2>
			<ol class="me-formation__program"><li><?php echo esc_html__( 'Apri la scheda del corso di interesse.', 'mediacon-enterprise' ); ?></li><li><?php echo esc_html__( 'Verifica date, modalità, requisiti e disponibilità pubblicati.', 'mediacon-enterprise' ); ?></li><li><?php echo esc_html__( 'Utilizza il collegamento di iscrizione indicato nella scheda.', 'mediacon-enterprise' ); ?></li></ol>
			<?php
			if ( have_posts() ) :
				?>
				<?php
				while ( have_posts() ) {
								the_post();
								the_content(); }
				?>
<?php endif; ?>
			<?php
			if ( $general['enrollment_url'] ) :
				?>
				<p><a class="me-formation__button" href="<?php echo esc_url( $general['enrollment_url'] ); ?>"><?php echo esc_html__( 'Vai al modulo di iscrizione', 'mediacon-enterprise' ); ?></a></p><?php endif; ?>
		</div>
	</section>
</main>
