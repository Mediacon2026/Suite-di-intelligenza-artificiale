<?php
/**
 * Formation administration page.
 *
 * @package MediaconEnterprise
 * @var array<string,array<string,mixed>> $rows Formation page rows.
 * @var array<string,mixed>               $general General settings.
 * @var array<int,WP_Post>                $pages Existing WordPress pages.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap me-formation-admin">
	<h1><?php esc_html_e( 'Mediacon Formazione', 'mediacon-enterprise' ); ?></h1>
	<p><?php esc_html_e( 'Collega contenuti WordPress esistenti e abilita esplicitamente le viste pubbliche del modulo.', 'mediacon-enterprise' ); ?></p>
	<p><strong><?php esc_html_e( 'Stato modulo:', 'mediacon-enterprise' ); ?></strong> <?php esc_html_e( 'attivo; ogni template resta in fallback WordPress finché non viene abilitato.', 'mediacon-enterprise' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="mediacon_enterprise_save_formation">
		<?php wp_nonce_field( 'mediacon_enterprise_save_formation', 'mediacon_enterprise_formation_nonce' ); ?>
		<h2><?php esc_html_e( 'Pagine pubbliche', 'mediacon-enterprise' ); ?></h2>
		<table class="widefat striped me-formation-admin__table">
			<thead>
				<tr><th><?php esc_html_e( 'Area', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Pagina collegata', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Template', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'URL', 'mediacon-enterprise' ); ?></th></tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $key => $row ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $row['label'] ); ?></strong></td>
					<td>
						<select name="formation[pages][<?php echo esc_attr( $key ); ?>]">
							<option value="0"><?php esc_html_e( 'Rileva dallo slug', 'mediacon-enterprise' ); ?></option>
							<?php foreach ( $pages as $available_page ) : ?>
								<option value="<?php echo esc_attr( (string) $available_page->ID ); ?>" <?php selected( $row['page_id'], $available_page->ID ); ?>><?php echo esc_html( $available_page->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td><label><input type="checkbox" name="formation[templates][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $row['enabled'] ); ?>> <?php echo esc_html( $row['enabled'] ? __( 'Template attivo', 'mediacon-enterprise' ) : __( 'Fallback WordPress', 'mediacon-enterprise' ) ); ?></label></td>
					<td>
					<?php if ( $row['url'] ) : ?>
						<a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $row['url'] ); ?></a>
					<?php else : ?>
						<span aria-label="<?php esc_attr_e( 'Pagina non trovata', 'mediacon-enterprise' ); ?>">—</span>
					<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Impostazioni generali', 'mediacon-enterprise' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr><th scope="row"><label for="me-course-category"><?php esc_html_e( 'Slug categoria corsi', 'mediacon-enterprise' ); ?></label></th><td><input class="regular-text" id="me-course-category" name="formation[general][course_category]" value="<?php echo esc_attr( $general['course_category'] ); ?>"></td></tr>
			<tr><th scope="row"><label for="me-teacher-category"><?php esc_html_e( 'Slug categoria docenti', 'mediacon-enterprise' ); ?></label></th><td><input class="regular-text" id="me-teacher-category" name="formation[general][teacher_category]" value="<?php echo esc_attr( $general['teacher_category'] ); ?>"></td></tr>
			<tr><th scope="row"><label for="me-insight-category"><?php esc_html_e( 'Slug categoria approfondimenti', 'mediacon-enterprise' ); ?></label></th><td><input class="regular-text" id="me-insight-category" name="formation[general][insight_category]" value="<?php echo esc_attr( $general['insight_category'] ); ?>"></td></tr>
			<tr><th scope="row"><label for="me-enrollment-url"><?php esc_html_e( 'URL iscrizioni', 'mediacon-enterprise' ); ?></label></th><td><input class="regular-text" type="url" id="me-enrollment-url" name="formation[general][enrollment_url]" value="<?php echo esc_attr( $general['enrollment_url'] ); ?>"></td></tr>
			<tr><th scope="row"><label for="me-posts-per-page"><?php esc_html_e( 'Elementi per pagina', 'mediacon-enterprise' ); ?></label></th><td><input class="small-text" type="number" min="3" max="24" id="me-posts-per-page" name="formation[general][posts_per_page]" value="<?php echo esc_attr( (string) $general['posts_per_page'] ); ?>"></td></tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Viste di dettaglio', 'mediacon-enterprise' ); ?></th>
				<td>
					<label><input type="checkbox" name="formation[general][detail_template]" value="1" <?php checked( $general['detail_template'] ); ?>> <?php esc_html_e( 'Usa il template Formazione per i corsi', 'mediacon-enterprise' ); ?></label><br>
					<label><input type="checkbox" name="formation[general][teacher_detail_template]" value="1" <?php checked( $general['teacher_detail_template'] ); ?>> <?php esc_html_e( 'Usa il template Formazione per i docenti', 'mediacon-enterprise' ); ?></label>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Salva impostazioni Formazione', 'mediacon-enterprise' ) ); ?>
	</form>
</div>
