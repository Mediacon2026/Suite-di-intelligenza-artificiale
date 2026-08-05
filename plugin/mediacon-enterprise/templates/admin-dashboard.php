<?php
/**
 * Administration dashboard template.
 *
 * @package MediaconEnterprise
 *
 * @var array<string,\Mediacon\Enterprise\Core\Module> $modules Registered modules.
 * @var array<int,string>                                 $enabled Enabled module identifiers.
 * @var string                                           $version Plugin version.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap mediacon-enterprise-admin">
	<h1><?php echo esc_html__( 'Mediacon Enterprise', 'mediacon-enterprise' ); ?></h1>
	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: plugin version. */
				__( 'Governance architetturale attiva. Versione %s.', 'mediacon-enterprise' ),
				$version
			)
		);
		?>
	</p>
	<div class="mediacon-enterprise-grid">
		<?php foreach ( $modules as $module ) : ?>
			<article class="mediacon-enterprise-card">
				<h2><?php echo esc_html( ucwords( str_replace( '-', ' ', $module->id() ) ) ); ?></h2>
				<p><?php echo esc_html( in_array( $module->id(), $enabled, true ) ? __( 'Modulo attivo e reversibile.', 'mediacon-enterprise' ) : __( 'Modulo disattivato; impostazioni conservate.', 'mediacon-enterprise' ) ); ?></p>
			</article>
		<?php endforeach; ?>
	</div>
</div>
