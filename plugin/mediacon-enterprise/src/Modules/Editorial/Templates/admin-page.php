<?php
/**
 * Editorial administration template.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap me-editorial-admin">
	<h1><?php esc_html_e( 'Mediacon Editoriale', 'mediacon-enterprise' ); ?></h1>
	<p><strong><?php esc_html_e( 'Stato modulo:', 'mediacon-enterprise' ); ?></strong> <?php esc_html_e( 'attivo; tutti i template restano in fallback WordPress finché non vengono abilitati.', 'mediacon-enterprise' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="mediacon_enterprise_save_editorial">
		<?php wp_nonce_field( 'mediacon_enterprise_save_editorial', 'mediacon_enterprise_editorial_nonce' ); ?>
		<h2><?php esc_html_e( 'Archivi e template', 'mediacon-enterprise' ); ?></h2>
		<table class="widefat striped me-editorial-admin__table">
			<thead><tr><th><?php esc_html_e( 'Area', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Categoria collegata', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Template', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'URL rilevato', 'mediacon-enterprise' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $rows as $key => $row ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $row['title'] ); ?></strong></td>
					<td>
					<?php if ( 'category' === $row['type'] || 'blog' === $key ) : ?>
						<select name="editorial[categories][<?php echo esc_attr( $key ); ?>]"><option value="0"><?php esc_html_e( 'Rilevamento automatico', 'mediacon-enterprise' ); ?></option>
						<?php
						foreach ( $categories as $available_category ) :
							?>
							<option value="<?php echo esc_attr( (string) $available_category->term_id ); ?>" <?php selected( $row['category_id'], $available_category->term_id ); ?>><?php echo esc_html( $available_category->name ); ?></option><?php endforeach; ?></select>
						<?php
					else :
						?>
						—<?php endif; ?>
					</td>
					<td><label><input type="checkbox" name="editorial[templates][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $row['enabled'] ); ?>> <?php echo esc_html( $row['enabled'] ? __( 'Template attivo', 'mediacon-enterprise' ) : __( 'Fallback WordPress', 'mediacon-enterprise' ) ); ?></label></td>
					<td>
					<?php
					if ( $row['url'] ) :
						?>
						<a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $row['url'] ); ?></a>
						<?php
else :
	?>
						—<?php endif; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<h2><?php esc_html_e( 'Impostazioni card', 'mediacon-enterprise' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr><th scope="row"><label for="me-title-length"><?php esc_html_e( 'Lunghezza massima titolo', 'mediacon-enterprise' ); ?></label></th><td><input id="me-title-length" type="number" min="30" max="140" name="editorial[general][title_length]" value="<?php echo esc_attr( (string) $general['title_length'] ); ?>"></td></tr>
			<tr><th scope="row"><label for="me-excerpt-length"><?php esc_html_e( 'Lunghezza massima estratto', 'mediacon-enterprise' ); ?></label></th><td><input id="me-excerpt-length" type="number" min="80" max="320" name="editorial[general][excerpt_length]" value="<?php echo esc_attr( (string) $general['excerpt_length'] ); ?>"></td></tr>
			<tr><th scope="row"><label for="me-image-ratio"><?php esc_html_e( 'Rapporto immagine', 'mediacon-enterprise' ); ?></label></th><td><select id="me-image-ratio" name="editorial[general][image_ratio]"><option value="16-9" <?php selected( $general['image_ratio'], '16-9' ); ?>>16:9</option><option value="4-3" <?php selected( $general['image_ratio'], '4-3' ); ?>>4:3</option><option value="1-1" <?php selected( $general['image_ratio'], '1-1' ); ?>>1:1</option></select></td></tr>
			<tr><th scope="row"><label for="me-columns"><?php esc_html_e( 'Numero colonne', 'mediacon-enterprise' ); ?></label></th><td><input id="me-columns" type="number" min="2" max="4" name="editorial[general][columns]" value="<?php echo esc_attr( (string) $general['columns'] ); ?>"></td></tr>
			<tr><th scope="row"><label for="me-posts-per-page"><?php esc_html_e( 'Articoli per pagina', 'mediacon-enterprise' ); ?></label></th><td><input id="me-posts-per-page" type="number" min="3" max="24" name="editorial[general][posts_per_page]" value="<?php echo esc_attr( (string) $general['posts_per_page'] ); ?>"></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Singolo articolo', 'mediacon-enterprise' ); ?></th><td><label><input type="checkbox" name="editorial[general][single_template]" value="1" <?php checked( $general['single_template'] ); ?>> <?php esc_html_e( 'Abilita il template Editoriale per gli articoli non gestiti da Formazione', 'mediacon-enterprise' ); ?></label></td></tr>
		</table>
		<?php submit_button( __( 'Salva impostazioni Editoriale', 'mediacon-enterprise' ) ); ?>
	</form>
	<h2><?php esc_html_e( 'Segnalazioni qualità contenuti', 'mediacon-enterprise' ); ?></h2>
	<p><?php esc_html_e( 'Controllo informativo sugli articoli recenti: nessun contenuto viene modificato automaticamente.', 'mediacon-enterprise' ); ?></p>
	<?php if ( $audit ) : ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Contenuto', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Segnalazioni', 'mediacon-enterprise' ); ?></th></tr></thead><tbody>
		<?php
		foreach ( $audit as $result ) :
			?>
			<tr><td><a href="<?php echo esc_url( get_edit_post_link( $result['post']->ID ) ); ?>"><?php echo esc_html( get_the_title( $result['post'] ) ); ?></a></td><td><?php echo esc_html( implode( '; ', $result['warnings'] ) ); ?></td></tr><?php endforeach; ?></tbody></table>
		<?php
	else :
		?>
		<p><?php esc_html_e( 'Nessuna segnalazione nei contenuti controllati.', 'mediacon-enterprise' ); ?></p><?php endif; ?>
</div>
