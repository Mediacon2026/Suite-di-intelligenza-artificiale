<?php
/**
 * Enterprise compatibility and diagnostics dashboard.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$yes_no   = static fn ( bool $value ): string => $value ? __( 'Sì', 'mediacon-enterprise' ) : __( 'No', 'mediacon-enterprise' );
$feedback = get_transient( 'mediacon_enterprise_migration_' . get_current_user_id() );
if ( false !== $feedback ) {
	delete_transient( 'mediacon_enterprise_migration_' . get_current_user_id() );
}
?>
<div class="wrap mediacon-enterprise-admin">
	<h1><?php esc_html_e( 'Mediacon Enterprise — Compatibilità', 'mediacon-enterprise' ); ?></h1>
	<p><?php esc_html_e( 'Analisi in sola lettura dei plugin legacy, dei contratti runtime, delle pagine e dei template intercettati.', 'mediacon-enterprise' ); ?></p>
	<?php
	if ( is_array( $feedback ) ) :
		?>
		<div class="notice <?php echo ! empty( $feedback['success'] ) ? 'notice-success' : 'notice-error'; ?> is-dismissible"><p><?php echo esc_html( (string) $feedback['message'] ); ?></p></div><?php endif; ?>
	<h2><?php esc_html_e( 'Matrice plugin legacy', 'mediacon-enterprise' ); ?></h2>
	<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Plugin Legacy', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Versione', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Attivo', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Compatibile', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Hook trovati', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Funzioni mancanti', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Costanti mancanti', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Classi mancanti', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Template intercettati', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Pagine gestite', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Fatal intercettati', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Warning', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Migrazione', 'mediacon-enterprise' ); ?></th></tr></thead><tbody>
	<?php
	if ( empty( $report['plugins'] ) ) :
		?>
		<tr><td colspan="13"><?php esc_html_e( 'Nessun plugin legacy Mediacon rilevato.', 'mediacon-enterprise' ); ?></td></tr><?php endif; ?>
	<?php foreach ( $report['plugins'] as $file => $legacy_plugin ) : ?>
		<tr><th scope="row"><?php echo esc_html( (string) ( $legacy_plugin['name'] ?? $file ) ); ?><br><code><?php echo esc_html( $file ); ?></code></th><td><?php echo esc_html( (string) ( $legacy_plugin['version'] ?? '' ) ); ?></td><td><?php echo esc_html( $yes_no( ! empty( $legacy_plugin['active'] ) ) ); ?></td><td><?php echo esc_html( $yes_no( ! empty( $legacy_plugin['compatible'] ) ) ); ?></td><td><?php echo esc_html( implode( ', ', (array) ( $legacy_plugin['hooks'] ?? array() ) ) ); ?></td><td><?php echo esc_html( implode( ', ', (array) ( $legacy_plugin['missing_functions'] ?? array() ) ) ); ?></td><td><?php echo esc_html( implode( ', ', (array) ( $legacy_plugin['missing_constants'] ?? array() ) ) ); ?></td><td><?php echo esc_html( implode( ', ', (array) ( $legacy_plugin['missing_classes'] ?? array() ) ) ); ?></td><td><?php echo esc_html( implode( ', ', (array) ( $legacy_plugin['templates'] ?? array() ) ) ); ?></td><td><?php echo esc_html( implode( ', ', array_map( static fn ( array $page ): string => (string) $page['title'], (array) ( $legacy_plugin['pages'] ?? array() ) ) ) ); ?></td><td><?php echo esc_html( (string) $runtime_counts['fatal'] ); ?></td><td><?php echo esc_html( (string) $runtime_counts['warnings'] ); ?></td><td>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="mediacon_enterprise_legacy_migration"><input type="hidden" name="plugin" value="<?php echo esc_attr( $file ); ?>"><?php wp_nonce_field( 'mediacon_enterprise_legacy_migration', 'mediacon_enterprise_migration_nonce' ); ?><div class="button-group">
		<?php
		foreach ( array(
			'analyze'  => __( 'Analizza', 'mediacon-enterprise' ),
			'migrate'  => __( 'Migra', 'mediacon-enterprise' ),
			'rollback' => __( 'Rollback', 'mediacon-enterprise' ),
			'verify'   => __( 'Verifica', 'mediacon-enterprise' ),
		) as $operation => $label ) :
			?>
								<button class="button" type="submit" name="operation" value="<?php echo esc_attr( $operation ); ?>"><?php echo esc_html( $label ); ?></button><?php endforeach; ?></div></form>
		</td></tr>
	<?php endforeach; ?></tbody></table>

	<h2><?php esc_html_e( 'Diagnostica', 'mediacon-enterprise' ); ?></h2>
	<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Gravità', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Causa', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Plugin', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Pagina', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Soluzione proposta', 'mediacon-enterprise' ); ?></th></tr></thead><tbody>
	<?php
	if ( empty( $issues ) ) :
		?>
		<tr><td colspan="5"><?php esc_html_e( 'Nessun problema rilevato.', 'mediacon-enterprise' ); ?></td></tr><?php endif; ?>
		<?php
		foreach ( $issues as $issue ) :
			?>
		<tr><td><strong><?php echo esc_html( strtoupper( $issue['gravita'] ) ); ?></strong></td><td><?php echo esc_html( $issue['causa'] ); ?></td><td><?php echo esc_html( $issue['plugin'] ); ?></td><td><?php echo esc_html( $issue['pagina'] ); ?></td><td><?php echo esc_html( $issue['soluzione'] ); ?></td></tr><?php endforeach; ?></tbody></table>

	<details><summary><?php esc_html_e( 'Contratti runtime Enterprise', 'mediacon-enterprise' ); ?></summary>
	<?php
	foreach ( array( 'constants', 'functions', 'classes', 'hooks', 'assets', 'pages', 'templates', 'missing_functions', 'missing_classes', 'collisions', 'errors' ) as $section ) :
		?>
		<h3><?php echo esc_html( ucwords( str_replace( '_', ' ', $section ) ) ); ?></h3><pre><?php echo esc_html( (string) wp_json_encode( $report[ $section ] ?? array(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre><?php endforeach; ?></details>
	<h2><?php esc_html_e( 'Bridge Design Core', 'mediacon-enterprise' ); ?></h2>
	<?php
	if ( $archive_ready ) :
		?>
		<p><a class="button" href="<?php echo esc_url( $download_url ); ?>"><?php esc_html_e( 'Scarica il plugin-ponte', 'mediacon-enterprise' ); ?></a> <code><?php echo esc_html( $archive_hash ); ?></code></p>
		<?php
else :
	?>
		<p><?php esc_html_e( 'Archivio bridge non disponibile.', 'mediacon-enterprise' ); ?></p><?php endif; ?>
</div>
