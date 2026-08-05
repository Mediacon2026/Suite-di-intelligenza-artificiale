<?php
/**
 * Editorial archive template.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;

$archive_key            = (string) $archive['key'];
$editorial_descriptions = array(
	'blog'          => __( 'Notizie, analisi e contributi pubblicati dalla redazione Mediacon.', 'mediacon-enterprise' ),
	'jurisprudence' => __( 'Decisioni e orientamenti giurisprudenziali disponibili nell’archivio Mediacon.', 'mediacon-enterprise' ),
	'legislation'   => __( 'Norme e riferimenti legislativi organizzati per una consultazione chiara.', 'mediacon-enterprise' ),
	'insights'      => __( 'Approfondimenti sui temi della mediazione, del diritto e della formazione.', 'mediacon-enterprise' ),
	'search'        => __( 'Ricerca nei contenuti editoriali pubblicati sul sito Mediacon.', 'mediacon-enterprise' ),
);
$components->renderComponent(
	'hero',
	array(
		'title'       => $archive['title'],
		'description' => $editorial_descriptions[ $archive_key ] ?? '',
	)
);
$components->renderComponent( 'breadcrumb', array( 'current' => $archive['title'] ) );
?>
<main id="main-content" class="me-editorial" style="--me-editorial-columns: <?php echo esc_attr( (string) $cards->general()['columns'] ); ?>">
	<section class="me-editorial__section">
		<div class="me-editorial__container">
			<?php
			$components->renderComponent(
				'filters',
				array(
					'action'      => '' !== $archive['url'] ? $archive['url'] : home_url( '/' ),
					'archive_key' => $archive_key,
					'facets'      => $facets,
					'catalog'     => $catalog,
				)
			);
			?>
			<?php if ( $query->have_posts() ) : ?>
				<div class="me-editorial__grid">
				<?php while ( $query->have_posts() ) : ?>
					<?php $query->the_post(); ?>
					<?php $components->renderComponent( 'card', array( 'card' => $cards->present( get_the_ID(), $archive_key ) ) ); ?>
				<?php endwhile; ?>
				</div>
				<?php
				if ( $query->max_num_pages > 1 ) :
					?>
					<nav class="me-editorial__pagination" aria-label="<?php esc_attr_e( 'Paginazione editoriale', 'mediacon-enterprise' ); ?>">
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
					</nav><?php endif; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="me-editorial__empty" role="status"><h2><?php esc_html_e( 'Nessun contenuto trovato', 'mediacon-enterprise' ); ?></h2><p><?php esc_html_e( 'Modifica i filtri o prova una ricerca differente.', 'mediacon-enterprise' ); ?></p></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	$components->renderComponent(
		'cta',
		array(
			'title'       => __( 'Hai bisogno di informazioni?', 'mediacon-enterprise' ),
			'description' => __( 'Consulta i servizi Mediacon o contatta la sede più vicina.', 'mediacon-enterprise' ),
			'label'       => __( 'Contatta Mediacon', 'mediacon-enterprise' ),
			'url'         => home_url( '/contatti/' ),
		)
	);
	?>
</main>
