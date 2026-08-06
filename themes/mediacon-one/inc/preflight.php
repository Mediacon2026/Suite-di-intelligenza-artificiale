<?php
/**
 * Read-only migration Preflight in Appearance > Mediacon One.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

/** Register the read-only Appearance submenu. */
function mediacon_one_register_preflight(): void {
	add_theme_page(
		__( 'Mediacon One Preflight', 'mediacon-one' ),
		__( 'Mediacon One', 'mediacon-one' ),
		'edit_theme_options',
		'mediacon-one',
		'mediacon_one_render_preflight'
	);
}
add_action( 'admin_menu', 'mediacon_one_register_preflight' );

/**
 * Create a normalized issue row.
 *
 * @param string $problem  Problem description.
 * @param string $page     Affected page or area.
 * @param string $severity Issue severity.
 * @param string $source   Rendering source.
 * @param string $plugin   Involved plugin or system.
 * @param string $solution Recommended manual solution.
 * @return array<string,string>
 */
function mediacon_one_preflight_issue( string $problem, string $page, string $severity, string $source, string $plugin, string $solution ): array {
	return array(
		'problem'  => $problem,
		'page'     => $page,
		'severity' => $severity,
		'source'   => $source,
		'plugin'   => $plugin,
		'solution' => $solution,
	);
}

/**
 * Return the expected rendering source for a configured page.
 *
 * @param string       $key  Configured resource key.
 * @param WP_Post|null $page Resolved page.
 */
function mediacon_one_preflight_rendering_source( string $key, ?WP_Post $page ): string {
	if ( 'costs' === $key && mediacon_one_preventivo_enabled() ) {
		$config = mediacon_one_enterprise_settings()['preventivo'] ?? array();
		if ( $page instanceof WP_Post && absint( $config['page_id'] ?? 0 ) === (int) $page->ID ) {
			return 'Mediacon Enterprise / Preventivo';
		}
	}

	return 'WordPress the_content()';
}

/**
 * Count saved H1 headings without modifying content.
 *
 * @param string $content Saved WordPress content.
 */
function mediacon_one_preflight_h1_count( string $content ): int {
	return preg_match_all( '/<h1(?:\s[^>]*)?>/i', $content );
}

/**
 * Find invalid document anchors in saved content.
 *
 * @param string $content Saved WordPress content.
 */
function mediacon_one_preflight_invalid_document_links( string $content ): int {
	$count = 0;
	if ( ! preg_match_all( '/<a\b([^>]*)>(.*?)<\/a>/is', $content, $anchors, PREG_SET_ORDER ) ) {
		return 0;
	}

	foreach ( $anchors as $anchor ) {
		$attributes  = (string) $anchor[1];
		$label       = wp_strip_all_tags( (string) $anchor[2] );
		$is_document = (bool) preg_match( '/\b(pdf|docx?|odt|scarica|download|allegato|modulo|curriculum)\b/i', $attributes . ' ' . $label );
		if ( ! $is_document ) {
			continue;
		}

		$href = '';
		if ( preg_match( '/\bhref\s*=\s*(["\'])(.*?)\1/is', $attributes, $match ) ) {
			$href = trim( html_entity_decode( (string) $match[2], ENT_QUOTES, 'UTF-8' ) );
		}
		if ( '' === $href || '#' === $href ) {
			++$count;
		}
	}

	return $count;
}

/** Collect every Preflight issue using read-only WordPress APIs. */
function mediacon_one_run_preflight(): array {
	$issues   = array();
	$page_map = mediacon_one_page_map();
	$pages    = array();

	foreach ( $page_map as $key => $resource ) {
		$path = (string) ( $resource['path'] ?? $key );
		if ( 'category' === ( $resource['type'] ?? '' ) ) {
			if ( ! mediacon_one_resolve_resource( $resource ) instanceof WP_Term ) {
				$issues[] = mediacon_one_preflight_issue( 'Categoria richiesta non trovata.', 'category/' . $path, 'Alta', 'Archivio WordPress', 'WordPress', 'Creare o associare manualmente la categoria prima dell’attivazione; non cambiare lo slug esistente.' );
			}
			continue;
		}

		$page          = mediacon_one_resolve_page( $resource );
		$pages[ $key ] = $page;
		$source        = mediacon_one_preflight_rendering_source( (string) $key, $page );
		if ( ! $page instanceof WP_Post ) {
			$issues[] = mediacon_one_preflight_issue( 'Pagina richiesta non trovata o pagina iniziale statica non assegnata.', $path, 'Critica', $source, 'WordPress', 'Associare manualmente una pagina esistente alla mappa configurabile; non creare contenuti automaticamente.' );
			continue;
		}
		if ( 'front_page' === ( $resource['type'] ?? '' ) && (int) get_option( 'page_on_front', 0 ) !== (int) $page->ID ) {
			$issues[] = mediacon_one_preflight_issue( 'La pagina Home esiste ma non è assegnata come pagina iniziale statica.', $path, 'Critica', $source, 'WordPress', 'Assegnare manualmente la pagina in Impostazioni > Lettura senza cambiarne URL o permalink.' );
		}

		if ( 'publish' !== $page->post_status ) {
			$issues[] = mediacon_one_preflight_issue( 'La pagina non è pubblicata.', $path, 'Alta', $source, 'WordPress', 'Verificare e pubblicare manualmente la pagina quando approvata.' );
		}
		$content = (string) $page->post_content;
		if ( '' === trim( wp_strip_all_tags( strip_shortcodes( $content ) ) ) ) {
			$issues[] = mediacon_one_preflight_issue( 'Contenuto WordPress vuoto.', $path, 'Alta', $source, 'WordPress', 'Inserire e approvare il contenuto in WordPress; il tema manterrà il fallback originale.' );
		}

		$h1_count = mediacon_one_preflight_h1_count( $content );
		if ( 0 < $h1_count ) {
			$severity = 1 < $h1_count ? 'Alta' : 'Media';
			$issues[] = mediacon_one_preflight_issue( sprintf( 'Il contenuto salvato contiene %d H1; il tema li presenta come H2 per mantenere un solo H1.', $h1_count ), $path, $severity, $source, 'WordPress/editor', 'Correggere manualmente la gerarchia dei titoli nel contenuto; nessuna riscrittura viene salvata dal tema.' );
		}

		$invalid_links = mediacon_one_preflight_invalid_document_links( $content );
		if ( 0 < $invalid_links ) {
			$issues[] = mediacon_one_preflight_issue( sprintf( '%d link a documenti hanno href vuoto o #.', $invalid_links ), $path, 'Critica', $source, 'WordPress/editor', 'Ripristinare manualmente gli URL dei documenti prima dell’attivazione.' );
		}
	}

	$menu_locations = get_nav_menu_locations();
	foreach ( array(
		'primary' => 'Critica',
		'footer'  => 'Media',
		'mega'    => 'Media',
	) as $location => $severity ) {
		if ( empty( $menu_locations[ $location ] ) ) {
			$issues[] = mediacon_one_preflight_issue( 'Menu non assegnato alla posizione ' . $location . '.', 'Navigazione', $severity, 'Menu WordPress', 'WordPress', 'Assegnare manualmente un menu esistente in Aspetto > Menu.' );
		}
	}

	if ( ! mediacon_one_enterprise_active() ) {
		$issues[] = mediacon_one_preflight_issue( 'Mediacon Enterprise non è attivo; restano disponibili i fallback WordPress.', 'Integrazioni', 'Media', 'WordPress fallback', 'Mediacon Enterprise', 'Attivare e verificare manualmente il plugin solo quando la configurazione è stata approvata.' );
	}

	$costs_page = $pages['costs'] ?? null;
	if ( ! mediacon_one_preventivo_enabled() ) {
		$issues[] = mediacon_one_preflight_issue( 'Preventivo Enterprise non attivo o privo di pagina configurata.', 'costi-della-mediazione', 'Alta', 'WordPress the_content()', 'Mediacon Enterprise / Preventivo', 'Abilitare manualmente modulo, frontend e pagina Costi; fino ad allora resta il contenuto WordPress.' );
	} elseif ( $costs_page instanceof WP_Post ) {
		$config = mediacon_one_enterprise_settings()['preventivo'] ?? array();
		if ( absint( $config['page_id'] ?? 0 ) !== (int) $costs_page->ID ) {
			$issues[] = mediacon_one_preflight_issue( 'Preventivo Enterprise associato a una pagina diversa da Costi.', 'costi-della-mediazione', 'Critica', 'WordPress the_content()', 'Mediacon Enterprise / Preventivo', 'Associare manualmente il modulo alla pagina Costi corretta.' );
		}
	}

	if ( ! mediacon_one_enterprise_search_enabled() ) {
		$issues[] = mediacon_one_preflight_issue( 'Ricerca Enterprise non attiva o priva di pagina; è in uso la ricerca WordPress.', 'Ricerca', 'Media', 'Ricerca WordPress', 'Mediacon Enterprise / Search', 'Verificare la ricerca WordPress oppure abilitare e associare manualmente il modulo Enterprise.' );
	}

	if ( $costs_page instanceof WP_Post && preg_match( '/\[(?:mediacon_calculator|mediacon_calcolatore|calcolatore_mediazione|mediacon_preventivo)\b|ripartizione interna|quota sede|totale pratica/i', (string) $costs_page->post_content ) ) {
		$issues[] = mediacon_one_preflight_issue( 'Rilevato il calcolatore legacy o testo con totale pratica/ripartizioni interne.', 'costi-della-mediazione', 'Critica', mediacon_one_preflight_rendering_source( 'costs', $costs_page ), 'Calcolatore legacy', 'Rimuovere manualmente il blocco legacy dal contenuto dopo aver verificato Preventivo Enterprise; il fallback tema ne sopprime solo gli shortcode noti.' );
	}

	$office_pages = array_filter(
		array(
			'contatti'       => $pages['contacts'] ?? null,
			'le-nostre-sedi' => $pages['offices'] ?? null,
		)
	);
	foreach ( mediacon_one_offices() as $office ) {
		$name    = (string) ( $office['name'] ?? '' );
		$address = (string) ( $office['address'] ?? '' );
		$status  = (string) ( $office['status'] ?? '' );
		foreach ( $office_pages as $path => $page ) {
			$content = wp_strip_all_tags( (string) $page->post_content );
			if ( '' !== $name && false === stripos( $content, $name ) ) {
				$issues[] = mediacon_one_preflight_issue( 'Sede configurata assente dalla pagina: ' . $name . '.', $path, 'Alta', 'WordPress the_content()', 'WordPress/editor', 'Allineare manualmente la pagina alla sorgente sedi del tema.' );
			}
			if ( 'operational' === $status && '' !== $address ) {
				$street = trim( (string) strtok( $address, ',' ) );
				if ( '' !== $street && false === stripos( $content, $street ) ) {
					$issues[] = mediacon_one_preflight_issue( 'Indirizzo operativo mancante o incoerente per ' . $name . '.', $path, 'Alta', 'WordPress the_content()', 'WordPress/editor', 'Verificare l’indirizzo approvato e uniformare manualmente le pagine.' );
				}
			}
		}
	}

	return $issues;
}

/** Render the administrative report without forms or write actions. */
function mediacon_one_render_preflight(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Non hai i permessi per visualizzare questo report.', 'mediacon-one' ) );
	}

	$issues = mediacon_one_run_preflight();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mediacon One — Preflight migrazione live', 'mediacon-one' ); ?></h1>
		<p><?php esc_html_e( 'Controllo in sola lettura: non modifica contenuti, URL, menu, opzioni o database.', 'mediacon-one' ); ?></p>
		<p><strong><?php /* translators: %d: number of Preflight issues. */ echo esc_html( sprintf( _n( '%d criticità rilevata.', '%d criticità rilevate.', count( $issues ), 'mediacon-one' ), count( $issues ) ) ); ?></strong></p>
		<?php if ( $issues ) : ?>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Criticità', 'mediacon-one' ); ?></th><th><?php esc_html_e( 'Pagina', 'mediacon-one' ); ?></th><th><?php esc_html_e( 'Gravità', 'mediacon-one' ); ?></th><th><?php esc_html_e( 'Sorgente di rendering', 'mediacon-one' ); ?></th><th><?php esc_html_e( 'Plugin coinvolto', 'mediacon-one' ); ?></th><th><?php esc_html_e( 'Soluzione consigliata', 'mediacon-one' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $issues as $issue ) : ?>
				<tr><td><?php echo esc_html( $issue['problem'] ); ?></td><td><?php echo esc_html( $issue['page'] ); ?></td><td><strong><?php echo esc_html( $issue['severity'] ); ?></strong></td><td><?php echo esc_html( $issue['source'] ); ?></td><td><?php echo esc_html( $issue['plugin'] ); ?></td><td><?php echo esc_html( $issue['solution'] ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php else : ?>
			<div class="notice notice-success inline"><p><?php esc_html_e( 'Nessuna criticità rilevata dai controlli automatici. Completare comunque la verifica visuale e funzionale prima dell’attivazione.', 'mediacon-one' ); ?></p></div>
		<?php endif; ?>
	</div>
	<?php
}
