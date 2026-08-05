<?php
/**
 * Reusable call to action.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$cta_title = isset( $args['title'] ) ? (string) $args['title'] : '';
$cta_text  = isset( $args['text'] ) ? (string) $args['text'] : '';
$cta_label = isset( $args['label'] ) ? (string) $args['label'] : '';
$cta_url   = isset( $args['url'] ) ? (string) $args['url'] : '';
if ( '' === $cta_title ) {
	return;
}
?>
<aside class="cta"><div><h2><?php echo esc_html( $cta_title ); ?></h2>
<?php
if ( '' !== $cta_text ) :
	?>
	<p><?php echo esc_html( $cta_text ); ?></p><?php endif; ?></div>
	<?php
	if ( '' !== $cta_label && '' !== $cta_url ) :
		?>
	<a class="button button--light" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a><?php endif; ?></aside>
