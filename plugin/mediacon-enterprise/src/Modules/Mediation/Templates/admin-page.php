<?php
/**
 * Mediation module administration template.
 *
 * @package MediaconEnterprise
 * @var array<string,array<string,mixed>> $rows  Supported page rows.
 * @var array<int,\WP_Post>               $pages Existing WordPress pages.
 */

defined( 'ABSPATH' ) || exit;

$updated = filter_input( INPUT_GET, 'updated', FILTER_VALIDATE_BOOLEAN );
?>
<div class="wrap me-mediation-admin">
	<h1><?php echo esc_html__( 'Mediacon Enterprise — Mediazione', 'mediacon-enterprise' ); ?></h1>
	<p><?php echo esc_html__( 'Il modulo è attivo. I template sostitutivi intervengono soltanto sulle pagine collegate e abilitate qui sotto.', 'mediacon-enterprise' ); ?></p>

	<?php if ( $updated ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Impostazioni salvate.', 'mediacon-enterprise' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="mediacon_enterprise_save_mediation">
		<?php wp_nonce_field( 'mediacon_enterprise_save_mediation', 'mediacon_enterprise_nonce' ); ?>
		<table class="widefat striped me-mediation-admin__table">
			<thead>
				<tr>
					<th scope="col"><?php echo esc_html__( 'Area', 'mediacon-enterprise' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Pagina collegata', 'mediacon-enterprise' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'URL rilevato', 'mediacon-enterprise' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Visualizzazione', 'mediacon-enterprise' ); ?></th>
					<th scope="col"><?php echo esc_html__( 'Azioni', 'mediacon-enterprise' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $key => $row ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $row['title'] ); ?></th>
						<td>
							<label class="screen-reader-text" for="me-page-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $row['title'] ); ?></label>
							<select id="me-page-<?php echo esc_attr( $key ); ?>" name="mediation_pages[<?php echo esc_attr( $key ); ?>]">
								<option value="0"><?php echo esc_html__( 'Rilevamento automatico', 'mediacon-enterprise' ); ?></option>
								<?php foreach ( $pages as $wp_page ) : ?>
									<option value="<?php echo esc_attr( (string) $wp_page->ID ); ?>" <?php selected( $row['page_id'], $wp_page->ID ); ?>><?php echo esc_html( $wp_page->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<?php if ( $row['url'] ) : ?>
								<code><?php echo esc_html( $row['url'] ); ?></code>
							<?php else : ?>
								<span><?php echo esc_html__( 'Pagina non rilevata', 'mediacon-enterprise' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<label>
								<input type="checkbox" name="mediation_templates[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $row['enabled'] ); ?> <?php disabled( 0 === $row['page_id'] ); ?>>
								<?php echo esc_html( $row['enabled'] ? __( 'Template Mediazione attivo', 'mediacon-enterprise' ) : __( 'Contenuto WordPress originale', 'mediacon-enterprise' ) ); ?>
							</label>
						</td>
						<td>
							<?php if ( $row['url'] ) : ?>
								<a class="button" href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Apri pagina', 'mediacon-enterprise' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php submit_button( __( 'Salva impostazioni', 'mediacon-enterprise' ) ); ?>
	</form>
</div>
