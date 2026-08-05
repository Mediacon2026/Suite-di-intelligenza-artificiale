<?php
/**
 * Enterprise diagnostic report builder.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Enterprise;

defined( 'ABSPATH' ) || exit;

/** Normalizes compatibility findings into actionable diagnostic rows. */
final class DiagnosticReport {

	/**
	 * Create the diagnostic report.
	 *
	 * @param SiteInventory  $inventory Inventory service.
	 * @param RuntimeMonitor $monitor Runtime monitor.
	 */
	public function __construct( private readonly SiteInventory $inventory, private readonly RuntimeMonitor $monitor ) {}

	/** Build normalized diagnostic rows. @return array<int,array<string,string>> */
	public function issues(): array {
		$issues = array();
		foreach ( $this->inventory->plugins() as $plugin ) {
			foreach ( array(
				'missing_functions' => 'Funzione',
				'missing_constants' => 'Costante',
				'missing_classes'   => 'Classe',
			) as $field => $kind ) {
				foreach ( $plugin[ $field ] as $contract ) {
					$issues[] = $this->row( 'high', "$kind mancante: $contract", (string) $plugin['name'], '', __( 'Mantenere attivo il bridge compatibile o implementare il contratto prima della migrazione.', 'mediacon-enterprise' ) );
				}
			}
			foreach ( $plugin['pages'] as $page ) {
				if ( 'publish' !== $page['status'] ) {
					$issues[] = $this->row( 'medium', __( 'La pagina gestita non è pubblicata.', 'mediacon-enterprise' ), (string) $plugin['name'], (string) $page['title'], __( 'Verificare intenzionalmente lo stato editoriale; Enterprise non lo modifica.', 'mediacon-enterprise' ) );
				}
			}
		}
		foreach ( $this->monitor->events() as $event ) {
			$issues[] = $this->row(
				(string) ( $event['severity'] ?? 'warning' ),
				(string) ( $event['message'] ?? __( 'Errore runtime intercettato.', 'mediacon-enterprise' ) ),
				(string) ( $event['file'] ?? '' ),
				'',
				__( 'Correggere la causa nel plugin indicato e rieseguire Compatibility Smoke e Legacy Smoke.', 'mediacon-enterprise' )
			);
		}
		return $issues;
	}

	/** Count runtime fatals and warnings for the compatibility matrix. */
	public function runtimeCounts(): array {
		$fatal    = 0;
		$warnings = 0;
		foreach ( $this->monitor->events() as $event ) {
			$fatal    += ! empty( $event['fatal'] ) ? 1 : 0;
			$warnings += empty( $event['fatal'] ) ? 1 : 0;
		}
		return array(
			'fatal'    => $fatal,
			'warnings' => $warnings,
		);
	}

	/**
	 * Build one diagnostic row.
	 *
	 * @param string $severity Severity.
	 * @param string $cause Cause.
	 * @param string $plugin Plugin.
	 * @param string $page Page.
	 * @param string $solution Proposed solution.
	 * @return array<string,string>
	 */
	private function row( string $severity, string $cause, string $plugin, string $page, string $solution ): array {
		return array(
			'gravita'   => sanitize_key( $severity ),
			'causa'     => sanitize_text_field( $cause ),
			'plugin'    => sanitize_text_field( $plugin ),
			'pagina'    => sanitize_text_field( $page ),
			'soluzione' => sanitize_text_field( $solution ),
		);
	}
}
