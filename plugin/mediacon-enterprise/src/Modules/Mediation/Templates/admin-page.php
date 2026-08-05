<?php
/**
 * Mediazione architecture administration.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$module           = 'mediation';
$association_type = 'pages';
$association_name = 'mediation_pages';
?>
<div class="wrap mediacon-enterprise-admin"><h1><?php esc_html_e( 'Mediacon Enterprise — Mediazione', 'mediacon-enterprise' ); ?></h1><p><?php esc_html_e( 'Inventario automatico e controllo immediato e reversibile. Nessun contenuto, URL o file viene modificato.', 'mediacon-enterprise' ); ?></p>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="mediacon_enterprise_save_mediation"><?php wp_nonce_field( 'mediacon_enterprise_save_mediation', 'mediacon_enterprise_nonce' ); ?><?php require MEDIACON_ENTERPRISE_PATH . 'templates/governance-table.php'; ?><?php submit_button( __( 'Salva associazioni pagina', 'mediacon-enterprise' ) ); ?></form></div>
