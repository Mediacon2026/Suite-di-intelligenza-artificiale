<?php
/**
 * Enterprise settings template.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap mediacon-enterprise-admin">
	<h1><?php esc_html_e( 'Mediacon Enterprise — Impostazioni', 'mediacon-enterprise' ); ?></h1>
	<p><?php esc_html_e( 'Ogni modulo può essere disattivato e riattivato senza cancellare impostazioni, contenuti o URL.', 'mediacon-enterprise' ); ?></p>
	<?php
	if ( filter_input( INPUT_GET, 'updated', FILTER_VALIDATE_BOOLEAN ) ) :
		?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impostazioni salvate.', 'mediacon-enterprise' ); ?></p></div><?php endif; ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="mediacon_enterprise_save_settings">
		<?php wp_nonce_field( 'mediacon_enterprise_save_settings', 'mediacon_enterprise_settings_nonce' ); ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Modulo', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Attivo', 'mediacon-enterprise' ); ?></th></tr></thead><tbody>
		<?php
		foreach ( $modules as $module ) :
			?>
			<tr><td><?php echo esc_html( ucfirst( $module->id() ) ); ?></td><td><label><input type="checkbox" name="enabled_modules[]" value="<?php echo esc_attr( $module->id() ); ?>" <?php checked( in_array( $module->id(), $enabled, true ) ); ?>> <?php esc_html_e( 'Abilitato', 'mediacon-enterprise' ); ?></label></td></tr><?php endforeach; ?>
		</tbody></table>
		<h2><?php esc_html_e( 'Diagnostica e rimozione dati', 'mediacon-enterprise' ); ?></h2>
		<p><label><input type="checkbox" name="diagnostics_enabled" value="1" <?php checked( $diagnostics_enabled ); ?>> <?php esc_html_e( 'Registra warning e fatal in un log limitato e non sensibile', 'mediacon-enterprise' ); ?></label></p>
		<p><label><input type="checkbox" name="delete_on_uninstall" value="1" <?php checked( $delete_on_uninstall ); ?>> <?php esc_html_e( 'Consenti la rimozione dati soltanto insieme alla costante di sicurezza prevista', 'mediacon-enterprise' ); ?></label></p>
		<?php submit_button( __( 'Salva impostazioni', 'mediacon-enterprise' ) ); ?>
	</form>
</div>
