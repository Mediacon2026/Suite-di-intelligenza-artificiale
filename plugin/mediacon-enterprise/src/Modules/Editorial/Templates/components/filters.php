<?php
/**
 * Accessible editorial filters.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;

$selected_search    = filter_input( INPUT_GET, 'editorial_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$search_name        = 'search' === $archive_key ? 's' : 'editorial_search';
$selected_search    = ! is_string( $selected_search ) && 'search' === $archive_key ? filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : $selected_search;
$selected_category  = filter_input( INPUT_GET, 'editorial_category', FILTER_VALIDATE_INT );
$selected_year      = filter_input( INPUT_GET, 'editorial_year', FILTER_VALIDATE_INT );
$selected_topic     = filter_input( INPUT_GET, 'editorial_topic', FILTER_VALIDATE_INT );
$selected_authority = filter_input( INPUT_GET, 'editorial_authority', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_order     = filter_input( INPUT_GET, 'editorial_order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_type      = filter_input( INPUT_GET, 'editorial_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
?>
<form class="me-editorial__filters" method="get" action="<?php echo esc_url( $action ); ?>" role="search">
	<label><span><?php esc_html_e( 'Cerca', 'mediacon-enterprise' ); ?></span><input type="search" name="<?php echo esc_attr( $search_name ); ?>" value="<?php echo esc_attr( is_string( $selected_search ) ? $selected_search : '' ); ?>"></label>
	<?php
	if ( 'blog' === $archive_key || 'search' === $archive_key ) :
		?>
		<label><span><?php esc_html_e( 'Categoria', 'mediacon-enterprise' ); ?></span><select name="editorial_category"><option value="0"><?php esc_html_e( 'Tutte', 'mediacon-enterprise' ); ?></option>
		<?php
		foreach ( get_categories( array( 'hide_empty' => true ) ) as $filter_category ) :
			?>
		<option value="<?php echo esc_attr( (string) $filter_category->term_id ); ?>" <?php selected( $selected_category, $filter_category->term_id ); ?>><?php echo esc_html( $filter_category->name ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
	<?php
	if ( 'search' === $archive_key ) :
		?>
		<label><span><?php esc_html_e( 'Tipologia contenuto', 'mediacon-enterprise' ); ?></span><select name="editorial_type"><option value=""><?php esc_html_e( 'Tutte', 'mediacon-enterprise' ); ?></option>
		<?php
		foreach ( array( 'jurisprudence', 'legislation', 'insights' ) as $type_key ) :
			?>
				<?php
				if ( ! empty( $catalog[ $type_key ]['category_id'] ) ) :
					?>
	<option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $selected_type, $type_key ); ?>><?php echo esc_html( $catalog[ $type_key ]['title'] ); ?></option><?php endif; ?><?php endforeach; ?></select></label><?php endif; ?>
	<label><span><?php esc_html_e( 'Anno', 'mediacon-enterprise' ); ?></span><select name="editorial_year"><option value="0"><?php esc_html_e( 'Tutti', 'mediacon-enterprise' ); ?></option>
	<?php
	foreach ( $facets['years'] as $filter_year ) :
		?>
		<option value="<?php echo esc_attr( (string) $filter_year ); ?>" <?php selected( $selected_year, $filter_year ); ?>><?php echo esc_html( (string) $filter_year ); ?></option><?php endforeach; ?></select></label>
	<?php
	if ( $facets['topics'] ) :
		?>
		<label><span><?php echo esc_html( 'legislation' === $archive_key ? __( 'Materia', 'mediacon-enterprise' ) : __( 'Argomento', 'mediacon-enterprise' ) ); ?></span><select name="editorial_topic"><option value="0"><?php esc_html_e( 'Tutti', 'mediacon-enterprise' ); ?></option>
		<?php
		foreach ( $facets['topics'] as $topic ) :
			?>
		<option value="<?php echo esc_attr( (string) $topic->term_id ); ?>" <?php selected( $selected_topic, $topic->term_id ); ?>><?php echo esc_html( $topic->name ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
	<?php
	if ( $facets['authorities'] ) :
		?>
		<label><span><?php echo esc_html( 'jurisprudence' === $archive_key ? __( 'Organo giudicante', 'mediacon-enterprise' ) : __( 'Fonte', 'mediacon-enterprise' ) ); ?></span><select name="editorial_authority"><option value=""><?php esc_html_e( 'Tutte', 'mediacon-enterprise' ); ?></option>
		<?php
		foreach ( $facets['authorities'] as $authority ) :
			?>
		<option value="<?php echo esc_attr( $authority ); ?>" <?php selected( $selected_authority, $authority ); ?>><?php echo esc_html( $authority ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
	<label><span><?php esc_html_e( 'Ordina', 'mediacon-enterprise' ); ?></span><select name="editorial_order"><option value="newest" <?php selected( $selected_order, 'newest' ); ?>><?php esc_html_e( 'Più recenti', 'mediacon-enterprise' ); ?></option><option value="oldest" <?php selected( $selected_order, 'oldest' ); ?>><?php esc_html_e( 'Meno recenti', 'mediacon-enterprise' ); ?></option><option value="title" <?php selected( $selected_order, 'title' ); ?>><?php esc_html_e( 'Titolo', 'mediacon-enterprise' ); ?></option></select></label>
	<button class="me-editorial__button" type="submit"><?php esc_html_e( 'Applica filtri', 'mediacon-enterprise' ); ?></button>
</form>
