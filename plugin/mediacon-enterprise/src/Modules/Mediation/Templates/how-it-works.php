<?php
/**
 * Complete public template for the mediation process page.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content    Page content.
 * @var array<string,mixed> $catalog    Page catalog.
 * @var array<int,array<string,string>> $faq FAQ entries.
 * @var \Mediacon\Enterprise\Modules\Mediation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$timeline        = array(
	__( 'Deposito dell’istanza', 'mediacon-enterprise' ),
	__( 'Nomina del Mediatore', 'mediacon-enterprise' ),
	__( 'Convocazione', 'mediacon-enterprise' ),
	__( 'Primo incontro', 'mediacon-enterprise' ),
	__( 'Eventuale prosecuzione', 'mediacon-enterprise' ),
	__( 'Accordo o mancato accordo', 'mediacon-enterprise' ),
);
$costs_url       = ! empty( $catalog['costs']['url'] ) ? $catalog['costs']['url'] : home_url( '/costi-della-mediazione/' );
$estimate_url    = apply_filters( 'mediacon_enterprise_estimate_url', home_url( '/preventivo-mediazione/' ) );
$legislation_url = ! empty( $catalog['legislation']['url'] ) ? $catalog['legislation']['url'] : home_url( '/normativa/' );
$application_url = ! empty( $catalog['application']['url'] ) ? $catalog['application']['url'] : home_url( '/istanza-di-mediazione/' );

$components->renderComponent(
	'hero',
	array(
		'eyebrow' => $content['eyebrow'],
		'title'   => $content['title'],
		'intro'   => $content['intro'],
	)
);
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-mediation">
	<section class="me-mediation__section" aria-labelledby="me-process-title">
		<div class="me-mediation__container">
			<div class="me-mediation__measure">
				<h2 id="me-process-title"><?php echo esc_html__( 'Le fasi del percorso', 'mediacon-enterprise' ); ?></h2>
				<p><?php echo esc_html__( 'Ogni procedimento viene organizzato sulla base delle informazioni depositate e delle comunicazioni rivolte alle parti.', 'mediacon-enterprise' ); ?></p>
			</div>
			<ol class="me-mediation__timeline">
				<?php foreach ( $timeline as $index => $step ) : ?>
					<li>
						<span aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
						<strong><?php echo esc_html( $step ); ?></strong>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<?php
	$components->renderComponent(
		'cards',
		array(
			'title' => __( 'Perché scegliere il confronto', 'mediacon-enterprise' ),
			'items' => $content['benefits'],
		)
	);
	?>

	<section class="me-mediation__section me-mediation__section--accent" aria-labelledby="me-mediator-title">
		<div class="me-mediation__container me-mediation__split">
			<div>
				<p class="me-mediation__eyebrow"><?php echo esc_html__( 'Imparzialità e ascolto', 'mediacon-enterprise' ); ?></p>
				<h2 id="me-mediator-title"><?php echo esc_html__( 'Il ruolo del Mediatore', 'mediacon-enterprise' ); ?></h2>
			</div>
			<p class="me-mediation__lead"><?php echo esc_html( $content['mediator'] ); ?></p>
		</div>
	</section>

	<?php
	$components->renderComponent(
		'cards',
		array(
			'title' => __( 'I possibili esiti', 'mediacon-enterprise' ),
			'items' => $content['outcomes'],
		)
	);
	?>

	<section class="me-mediation__section me-mediation__section--muted" aria-labelledby="me-tools-title">
		<div class="me-mediation__container">
			<h2 id="me-tools-title"><?php echo esc_html__( 'Costi e strumenti', 'mediacon-enterprise' ); ?></h2>
			<div class="me-mediation__actions">
				<a class="me-mediation__button" href="<?php echo esc_url( $costs_url ); ?>"><?php echo esc_html__( 'Consulta i costi', 'mediacon-enterprise' ); ?></a>
				<a class="me-mediation__button me-mediation__button--secondary" href="<?php echo esc_url( $estimate_url ); ?>"><?php echo esc_html__( 'Richiedi un preventivo', 'mediacon-enterprise' ); ?></a>
			</div>
		</div>
	</section>

	<?php $components->renderComponent( 'faq', array( 'items' => $faq ) ); ?>

	<section class="me-mediation__section" aria-labelledby="me-rules-title">
		<div class="me-mediation__container me-mediation__measure">
			<p class="me-mediation__eyebrow"><?php echo esc_html__( 'Fonti e aggiornamenti', 'mediacon-enterprise' ); ?></p>
			<h2 id="me-rules-title"><?php echo esc_html__( 'Normativa essenziale', 'mediacon-enterprise' ); ?></h2>
			<p><?php echo esc_html__( 'Consulta la raccolta normativa pubblicata e aggiornata dalla redazione del sito.', 'mediacon-enterprise' ); ?></p>
			<a class="me-mediation__text-link" href="<?php echo esc_url( $legislation_url ); ?>"><?php echo esc_html__( 'Vai alla normativa', 'mediacon-enterprise' ); ?></a>
		</div>
	</section>

	<?php
	$components->renderComponent(
		'cta',
		array(
			'title'       => __( 'Avvia il percorso', 'mediacon-enterprise' ),
			'description' => __( 'Consulta le informazioni per predisporre e depositare l’istanza di mediazione.', 'mediacon-enterprise' ),
			'label'       => __( 'Vai all’istanza', 'mediacon-enterprise' ),
			'url'         => $application_url,
		)
	);
	?>
</main>
