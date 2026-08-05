<?php
/**
 * Reusable Enterprise governance table.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$association_type = $association_type ?? '';
?>
<table class="widefat striped mediacon-enterprise-governance">
	<thead><tr><th><?php esc_html_e( 'Titolo', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Slug', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'ID', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Template utilizzato', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Plugin legacy associato', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Modulo Enterprise associato', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Stato', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Gestione', 'mediacon-enterprise' ); ?></th><th><?php esc_html_e( 'Associazione / URL', 'mediacon-enterprise' ); ?></th></tr></thead>
	<tbody>
	<?php foreach ( $rows as $key => $row ) : ?>
		<tr>
			<th scope="row"><?php echo esc_html( $row['title'] ?? $key ); ?></th>
			<td><code><?php echo esc_html( (string) ( $row['slug'] ?? '' ) ); ?></code></td>
			<td><?php echo esc_html( (string) ( ! empty( $row['id'] ) ? $row['id'] : ( $row['category_id'] ?? 0 ) ) ); ?></td>
			<td><?php echo esc_html( (string) ( $row['wordpress_template'] ?? $row['type'] ?? '' ) ); ?></td>
			<td><?php echo esc_html( (string) ( $row['legacy_plugin'] ?? __( 'Nessuno rilevato', 'mediacon-enterprise' ) ) ); ?></td>
			<td><code><?php echo esc_html( (string) ( $row['enterprise_module'] ?? $module ) ); ?></code></td>
			<td><?php echo esc_html( (string) ( $row['status'] ?? __( 'Runtime', 'mediacon-enterprise' ) ) ); ?></td>
			<td>
				<select class="mediacon-governance-selector" data-module="<?php echo esc_attr( $module ); ?>" data-resource="<?php echo esc_attr( $key ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'mediacon_enterprise_page_governance' ) ); ?>">
					<option value="wordpress" <?php selected( $row['mode'], 'WordPress' ); ?>><?php esc_html_e( 'Gestione WordPress', 'mediacon-enterprise' ); ?></option>
					<option value="enterprise" <?php selected( $row['mode'], 'enterprise' ); ?>><?php esc_html_e( 'Gestione Enterprise', 'mediacon-enterprise' ); ?></option>
					<option value="legacy" <?php selected( $row['mode'], 'legacy' ); ?>><?php esc_html_e( 'Gestione Plugin Legacy', 'mediacon-enterprise' ); ?></option>
				</select>
			</td>
			<td>
			<?php if ( 'pages' === $association_type ) : ?>
				<select name="<?php echo esc_attr( $association_name . '[' . $key . ']' ); ?>"><option value="0"><?php esc_html_e( 'Rileva dallo slug', 'mediacon-enterprise' ); ?></option>
				<?php
				foreach ( $pages as $available_page ) :
					?>
					<option value="<?php echo esc_attr( (string) $available_page->ID ); ?>" <?php selected( $row['page_id'], $available_page->ID ); ?>><?php echo esc_html( $available_page->post_title ); ?></option><?php endforeach; ?></select>
			<?php elseif ( 'formation-pages' === $association_type ) : ?>
				<select name="formation[pages][<?php echo esc_attr( $key ); ?>]"><option value="0"><?php esc_html_e( 'Rileva dallo slug', 'mediacon-enterprise' ); ?></option>
				<?php
				foreach ( $pages as $available_page ) :
					?>
					<option value="<?php echo esc_attr( (string) $available_page->ID ); ?>" <?php selected( $row['page_id'], $available_page->ID ); ?>><?php echo esc_html( $available_page->post_title ); ?></option><?php endforeach; ?></select>
			<?php elseif ( 'categories' === $association_type && 'category' === ( $row['type'] ?? '' ) ) : ?>
				<select name="editorial[categories][<?php echo esc_attr( $key ); ?>]"><option value="0"><?php esc_html_e( 'Rilevamento automatico', 'mediacon-enterprise' ); ?></option>
				<?php
				foreach ( $categories as $available_category ) :
					?>
					<option value="<?php echo esc_attr( (string) $available_category->term_id ); ?>" <?php selected( $row['category_id'], $available_category->term_id ); ?>><?php echo esc_html( $available_category->name ); ?></option><?php endforeach; ?></select>
				<?php
			elseif ( ! empty( $row['url'] ) ) :
				?>
				<a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Apri', 'mediacon-enterprise' ); ?></a>
				<?php
else :
	?>
				—<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
