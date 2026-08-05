<?php
/**
 * Standard public mediation page template.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var array<string,mixed> $catalog Page catalog.
 * @var array<int,array<string,string>> $faq FAQ entries.
 * @var \Mediacon\Enterprise\Modules\Mediation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$application_url = ! empty( $catalog['application']['url'] ) ? $catalog['application']['url'] : home_url( '/istanza-di-mediazione/' );
$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-mediation">
	<?php if ( ! empty( $content['cards'] ) ) : ?>
		<?php
		$components->renderComponent(
			'cards',
			array(
				'title' => __( 'Informazioni utili', 'mediacon-enterprise' ),
				'items' => $content['cards'],
			)
		);
		?>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<section class="me-mediation__section">
			<div class="me-mediation__container me-mediation__measure me-mediation__prose">
				<?php
				while ( have_posts() ) {
					the_post();
					the_content();
				}
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php $components->renderComponent( 'faq', array( 'items' => $faq ) ); ?>
	<?php
	$components->renderComponent(
		'cta',
		array(
			'title'       => __( 'Hai bisogno di assistenza?', 'mediacon-enterprise' ),
			'description' => __( 'Consulta le indicazioni per presentare una nuova istanza di mediazione.', 'mediacon-enterprise' ),
			'label'       => __( 'Presenta un’istanza', 'mediacon-enterprise' ),
			'url'         => $application_url,
		)
	);
	?>
</main>
