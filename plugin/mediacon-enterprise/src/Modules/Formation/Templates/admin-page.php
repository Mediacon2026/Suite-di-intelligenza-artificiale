<?php
/**
 * Formazione architecture administration.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$module           = 'formation';
$association_type = 'formation-pages';
?>
<div class="wrap mediacon-enterprise-admin"><h1><?php esc_html_e( 'Mediacon Enterprise — Formazione', 'mediacon-enterprise' ); ?></h1><p><?php esc_html_e( 'La selezione di gestione è immediata e reversibile; le impostazioni editoriali restano separate.', 'mediacon-enterprise' ); ?></p>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="mediacon_enterprise_save_formation"><?php wp_nonce_field( 'mediacon_enterprise_save_formation', 'mediacon_enterprise_formation_nonce' ); ?><?php require MEDIACON_ENTERPRISE_PATH . 'templates/governance-table.php'; ?>
<h2><?php esc_html_e( 'Impostazioni generali esistenti', 'mediacon-enterprise' ); ?></h2><table class="form-table" role="presentation">
<tr><th><label for="me-course-category"><?php esc_html_e( 'Slug categoria corsi', 'mediacon-enterprise' ); ?></label></th><td><input id="me-course-category" name="formation[general][course_category]" value="<?php echo esc_attr( $general['course_category'] ); ?>"></td></tr>
<tr><th><label for="me-teacher-category"><?php esc_html_e( 'Slug categoria docenti', 'mediacon-enterprise' ); ?></label></th><td><input id="me-teacher-category" name="formation[general][teacher_category]" value="<?php echo esc_attr( $general['teacher_category'] ); ?>"></td></tr>
<tr><th><label for="me-insight-category"><?php esc_html_e( 'Slug categoria approfondimenti', 'mediacon-enterprise' ); ?></label></th><td><input id="me-insight-category" name="formation[general][insight_category]" value="<?php echo esc_attr( $general['insight_category'] ); ?>"></td></tr>
<tr><th><label for="me-enrollment-url"><?php esc_html_e( 'URL iscrizioni', 'mediacon-enterprise' ); ?></label></th><td><input type="url" id="me-enrollment-url" name="formation[general][enrollment_url]" value="<?php echo esc_attr( $general['enrollment_url'] ); ?>"></td></tr>
<tr><th><label for="me-formation-per-page"><?php esc_html_e( 'Elementi per pagina', 'mediacon-enterprise' ); ?></label></th><td><input type="number" min="3" max="24" id="me-formation-per-page" name="formation[general][posts_per_page]" value="<?php echo esc_attr( (string) $general['posts_per_page'] ); ?>"></td></tr>
<tr><th><?php esc_html_e( 'Viste di dettaglio', 'mediacon-enterprise' ); ?></th><td><label><input type="checkbox" name="formation[general][detail_template]" value="1" <?php checked( $general['detail_template'] ); ?>> <?php esc_html_e( 'Corsi', 'mediacon-enterprise' ); ?></label><br><label><input type="checkbox" name="formation[general][teacher_detail_template]" value="1" <?php checked( $general['teacher_detail_template'] ); ?>> <?php esc_html_e( 'Docenti', 'mediacon-enterprise' ); ?></label></td></tr></table><?php submit_button( __( 'Salva associazioni e impostazioni', 'mediacon-enterprise' ) ); ?></form></div>
