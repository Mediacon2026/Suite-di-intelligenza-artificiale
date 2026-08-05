<?php
/**
 * Preventivo settings form.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$config        = is_array( $config ?? null ) ? $config : array();
$bracket_lines = array_map( static fn ( array $row ): string => (string) ( $row['max'] ?? 0 ) . ' | ' . (string) ( $row['fee'] ?? 0 ), $config['brackets'] ?? array() );
?>
<div class="wrap mce-preventivo-admin">
	<h1><?php esc_html_e( 'Mediacon — Preventivo', 'mediacon-enterprise' ); ?></h1>
	<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success"><p><?php esc_html_e( 'Impostazioni salvate.', 'mediacon-enterprise' ); ?></p></div>
	<?php endif; ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="mediacon_enterprise_save_preventivo">
		<?php wp_nonce_field( 'mediacon_enterprise_save_preventivo', 'mediacon_enterprise_preventivo_nonce' ); ?>
		<h2><?php esc_html_e( 'Stato e frontend', 'mediacon-enterprise' ); ?></h2>
		<label><input type="checkbox" name="preventivo[frontend_enabled]" value="1" <?php checked( ! empty( $config['frontend_enabled'] ) ); ?>> <?php esc_html_e( 'Frontend attivo', 'mediacon-enterprise' ); ?></label>
		<p><label><?php esc_html_e( 'ID pagina', 'mediacon-enterprise' ); ?> <input type="number" min="0" name="preventivo[page_id]" value="<?php echo esc_attr( (string) ( $config['page_id'] ?? 0 ) ); ?>"></label></p>
		<h2><?php esc_html_e( 'Fasce tariffarie', 'mediacon-enterprise' ); ?></h2>
		<p><?php esc_html_e( 'Una riga per fascia: valore massimo | tariffa per centro. Usare 0 come ultima fascia senza limite.', 'mediacon-enterprise' ); ?></p>
		<textarea name="preventivo[brackets_text]" rows="12" class="large-text code"><?php echo esc_textarea( implode( "\n", $bracket_lines ) ); ?></textarea>
		<h2><?php esc_html_e( 'Riduzioni percentuali', 'mediacon-enterprise' ); ?></h2>
		<?php
		foreach ( array(
			'mandatory' => 'Obbligatoria e demandata',
			'voluntary' => 'Volontaria',
		) as $key => $label ) :
			?>
			<label><?php echo esc_html( $label ); ?> <input type="number" min="0" max="100" step="0.01" name="preventivo[reductions][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) ( $config['reductions'][ $key ] ?? 0 ) ); ?>"></label>
		<?php endforeach; ?>
		<input type="hidden" name="preventivo[reductions][court_ordered]" value="<?php echo esc_attr( (string) ( $config['reductions']['mandatory'] ?? 0 ) ); ?>">
		<h2><?php esc_html_e( 'Aumenti per scenario', 'mediacon-enterprise' ); ?></h2>
		<table class="form-table"><tbody>
		<?php foreach ( $scenarios as $key => $label ) : ?>
			<tr><th><?php echo esc_html( $label ); ?></th><td><input type="number" min="0" max="300" step="0.01" name="preventivo[increases][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) ( $config['increases'][ $key ] ?? 0 ) ); ?>">%</td></tr>
		<?php endforeach; ?>
		</tbody></table>
		<h2><?php esc_html_e( 'Spese vive predefinite', 'mediacon-enterprise' ); ?></h2>
		<?php
		foreach ( array(
			'registered_letter' => 'Raccomandata',
			'digital_signature' => 'Firma digitale',
			'extra_copy'        => 'Copia ulteriore verbale',
		) as $key => $label ) :
			?>
			<label><?php echo esc_html( $label ); ?> € <input type="number" min="0" step="0.01" name="preventivo[expenses][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) ( $config['expenses'][ $key ] ?? 0 ) ); ?>"></label>
		<?php endforeach; ?>
		<h2><?php esc_html_e( 'Testi e stampa', 'mediacon-enterprise' ); ?></h2>
		<p><label><?php esc_html_e( 'Testo esplicativo', 'mediacon-enterprise' ); ?><textarea class="large-text" name="preventivo[texts][explanation]"><?php echo esc_textarea( (string) ( $config['texts']['explanation'] ?? '' ) ); ?></textarea></label></p>
		<p><label><?php esc_html_e( 'Avvertenza', 'mediacon-enterprise' ); ?><textarea class="large-text" name="preventivo[texts][disclaimer]"><?php echo esc_textarea( (string) ( $config['texts']['disclaimer'] ?? '' ) ); ?></textarea></label></p>
		<p><label><?php esc_html_e( 'Header', 'mediacon-enterprise' ); ?> <input class="regular-text" name="preventivo[print][header]" value="<?php echo esc_attr( (string) ( $config['print']['header'] ?? '' ) ); ?>"></label></p>
		<p><label><?php esc_html_e( 'Footer', 'mediacon-enterprise' ); ?> <input class="regular-text" name="preventivo[print][footer]" value="<?php echo esc_attr( (string) ( $config['print']['footer'] ?? '' ) ); ?>"></label></p>
		<h2><?php esc_html_e( 'Numerazione simulazioni', 'mediacon-enterprise' ); ?></h2>
		<label><?php esc_html_e( 'Prefisso', 'mediacon-enterprise' ); ?> <input name="preventivo[simulation][prefix]" value="<?php echo esc_attr( (string) ( $config['simulation']['prefix'] ?? 'MC-PREV' ) ); ?>"></label>
		<label><?php esc_html_e( 'Prossimo numero', 'mediacon-enterprise' ); ?> <input type="number" min="1" name="preventivo[simulation][next_number]" value="<?php echo esc_attr( (string) ( $config['simulation']['next_number'] ?? 1 ) ); ?>"></label>
		<?php submit_button(); ?>
	</form>
</div>
