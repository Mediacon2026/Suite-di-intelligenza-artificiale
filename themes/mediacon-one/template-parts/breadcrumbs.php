<?php
/**
 * Accessible breadcrumbs.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
if ( is_front_page() ) {
	return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Percorso di navigazione', 'mediacon-one' ); ?>"><div class="container"><ol><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'mediacon-one' ); ?></a></li>
<?php
if ( is_singular( 'post' ) ) :
	$posts_url = get_post_type_archive_link( 'post' );
	$posts_url = $posts_url ? $posts_url : home_url( '/' );
	?>
	<li><a href="<?php echo esc_url( $posts_url ); ?>"><?php esc_html_e( 'Approfondimenti', 'mediacon-one' ); ?></a></li><?php endif; ?><li aria-current="page"><?php echo esc_html( wp_get_document_title() ); ?></li></ol></div></nav>
