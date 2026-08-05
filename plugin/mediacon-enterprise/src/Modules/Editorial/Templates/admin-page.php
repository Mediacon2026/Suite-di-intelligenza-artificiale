<?php
/**
 * Editorial architecture administration.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$module           = 'editorial';
$association_type = 'categories';
?>
<div class="wrap mediacon-enterprise-admin"><h1><?php esc_html_e( 'Mediacon Enterprise — Editoriale', 'mediacon-enterprise' ); ?></h1><p><?php esc_html_e( 'Archivi, categorie e template restano invariati finché non viene selezionata Gestione Enterprise.', 'mediacon-enterprise' ); ?></p>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="mediacon_enterprise_save_editorial"><?php wp_nonce_field( 'mediacon_enterprise_save_editorial', 'mediacon_enterprise_editorial_nonce' ); ?><?php require MEDIACON_ENTERPRISE_PATH . 'templates/governance-table.php'; ?>
<h2><?php esc_html_e( 'Impostazioni card esistenti', 'mediacon-enterprise' ); ?></h2><table class="form-table" role="presentation">
<tr><th><label for="me-title-length"><?php esc_html_e( 'Lunghezza titolo', 'mediacon-enterprise' ); ?></label></th><td><input id="me-title-length" type="number" min="30" max="140" name="editorial[general][title_length]" value="<?php echo esc_attr( (string) $general['title_length'] ); ?>"></td></tr>
<tr><th><label for="me-excerpt-length"><?php esc_html_e( 'Lunghezza estratto', 'mediacon-enterprise' ); ?></label></th><td><input id="me-excerpt-length" type="number" min="80" max="320" name="editorial[general][excerpt_length]" value="<?php echo esc_attr( (string) $general['excerpt_length'] ); ?>"></td></tr>
<tr><th><label for="me-image-ratio"><?php esc_html_e( 'Rapporto immagine', 'mediacon-enterprise' ); ?></label></th><td><select id="me-image-ratio" name="editorial[general][image_ratio]"><option value="16-9" <?php selected( $general['image_ratio'], '16-9' ); ?>>16:9</option><option value="4-3" <?php selected( $general['image_ratio'], '4-3' ); ?>>4:3</option><option value="1-1" <?php selected( $general['image_ratio'], '1-1' ); ?>>1:1</option></select></td></tr>
<tr><th><label for="me-columns"><?php esc_html_e( 'Colonne', 'mediacon-enterprise' ); ?></label></th><td><input id="me-columns" type="number" min="2" max="4" name="editorial[general][columns]" value="<?php echo esc_attr( (string) $general['columns'] ); ?>"></td></tr>
<tr><th><label for="me-editorial-per-page"><?php esc_html_e( 'Articoli per pagina', 'mediacon-enterprise' ); ?></label></th><td><input id="me-editorial-per-page" type="number" min="3" max="24" name="editorial[general][posts_per_page]" value="<?php echo esc_attr( (string) $general['posts_per_page'] ); ?>"></td></tr>
<tr><th><?php esc_html_e( 'Singolo articolo', 'mediacon-enterprise' ); ?></th><td><label><input type="checkbox" name="editorial[general][single_template]" value="1" <?php checked( $general['single_template'] ); ?>> <?php esc_html_e( 'Template Editoriale', 'mediacon-enterprise' ); ?></label></td></tr></table><?php submit_button( __( 'Salva associazioni e impostazioni', 'mediacon-enterprise' ) ); ?></form>
<h2><?php esc_html_e( 'Diagnostica qualità contenuti (sola lettura)', 'mediacon-enterprise' ); ?></h2>
<?php
if ( $audit ) :
	?>
	<table class="widefat striped"><tbody>
	<?php
	foreach ( $audit as $result ) :
		?>
	<tr><td><?php echo esc_html( get_the_title( $result['post'] ) ); ?></td><td><?php echo esc_html( implode( '; ', $result['warnings'] ) ); ?></td></tr><?php endforeach; ?></tbody></table>
	<?php
else :
	?>
	<p><?php esc_html_e( 'Nessuna segnalazione.', 'mediacon-enterprise' ); ?></p><?php endif; ?></div>
