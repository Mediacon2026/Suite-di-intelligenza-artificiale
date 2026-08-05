<?php
/**
 * Legacy compatibility diagnostics.
 *
 * @package MediaconEnterprise
 *
 * @var array<string,mixed> $report Diagnostics report.
 * @var bool                $archive_ready Archive availability.
 * @var string              $archive_hash SHA-256 digest.
 * @var string              $download_url Protected download URL.
 */

defined( 'ABSPATH' ) || exit;

$mediacon_status = static fn ( bool $value ): string => $value ? __( 'Sì', 'mediacon-enterprise' ) : __( 'No', 'mediacon-enterprise' );
?>
<div class="wrap mediacon-enterprise-admin">
	<h1><?php echo esc_html__( 'Compatibilità legacy', 'mediacon-enterprise' ); ?></h1>
	<p><?php echo esc_html__( 'Questo layer espone soltanto i contratti infrastrutturali documentati. I contratti applicativi legacy devono essere verificati sui relativi sorgenti PHP.', 'mediacon-enterprise' ); ?></p>

	<h2><?php echo esc_html__( 'Stato', 'mediacon-enterprise' ); ?></h2>
	<table class="widefat striped"><tbody>
		<tr><th><?php echo esc_html__( 'Mediacon Enterprise attivo', 'mediacon-enterprise' ); ?></th><td><?php echo esc_html( $mediacon_status( (bool) $report['enterprise_active'] ) ); ?></td></tr>
		<tr><th><?php echo esc_html__( 'Plugin-ponte installato', 'mediacon-enterprise' ); ?></th><td><?php echo esc_html( $mediacon_status( (bool) $report['bridge_installed'] ) ); ?></td></tr>
		<tr><th><?php echo esc_html__( 'Plugin-ponte attivo', 'mediacon-enterprise' ); ?></th><td><?php echo esc_html( $mediacon_status( (bool) $report['bridge_active'] ) ); ?></td></tr>
	</tbody></table>

	<h2><?php echo esc_html__( 'Plugin Mediacon', 'mediacon-enterprise' ); ?></h2>
	<table class="widefat striped"><thead><tr><th><?php echo esc_html__( 'Plugin', 'mediacon-enterprise' ); ?></th><th><?php echo esc_html__( 'Attivo', 'mediacon-enterprise' ); ?></th><th><?php echo esc_html__( 'Richiede mediacon-design-core', 'mediacon-enterprise' ); ?></th></tr></thead><tbody>
	<?php if ( array() === $report['plugins'] ) : ?>
		<tr><td colspan="3"><?php echo esc_html__( 'Nessun plugin Mediacon rilevato.', 'mediacon-enterprise' ); ?></td></tr>
	<?php else : ?>
		<?php foreach ( $report['plugins'] as $file => $mediacon_plugin ) : ?>
			<tr><td><?php echo esc_html( $mediacon_plugin['name'] . ' — ' . $file ); ?></td><td><?php echo esc_html( $mediacon_status( $mediacon_plugin['active'] ) ); ?></td><td><?php echo esc_html( $mediacon_status( $mediacon_plugin['requires_core'] ) ); ?></td></tr>
		<?php endforeach; ?>
	<?php endif; ?>
	</tbody></table>

	<h2><?php echo esc_html__( 'Contratti runtime', 'mediacon-enterprise' ); ?></h2>
	<?php foreach ( array( 'constants', 'functions', 'classes', 'hooks', 'assets', 'pages', 'templates', 'missing_functions', 'missing_classes', 'collisions', 'errors' ) as $section ) : ?>
		<h3><?php echo esc_html( ucwords( str_replace( '_', ' ', $section ) ) ); ?></h3>
		<pre><?php echo esc_html( (string) wp_json_encode( $report[ $section ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre>
	<?php endforeach; ?>

	<h2><?php echo esc_html__( 'Plugin-ponte', 'mediacon-enterprise' ); ?></h2>
	<p><?php echo esc_html__( 'Installa lo ZIP senza rinominare la cartella: deve risultare esattamente mediacon-design-core. Non sostituisce né duplica i servizi Enterprise.', 'mediacon-enterprise' ); ?></p>
	<?php if ( $archive_ready ) : ?>
		<p><a class="button button-primary" href="<?php echo esc_url( $download_url ); ?>"><?php echo esc_html__( 'Scarica il plugin-ponte', 'mediacon-enterprise' ); ?></a></p>
		<p><strong>SHA-256:</strong> <code><?php echo esc_html( $archive_hash ); ?></code></p>
	<?php else : ?>
		<div class="notice notice-warning inline"><p><?php echo esc_html__( 'L’archivio precompilato del plugin-ponte non è incluso in questa build.', 'mediacon-enterprise' ); ?></p></div>
	<?php endif; ?>
</div>
