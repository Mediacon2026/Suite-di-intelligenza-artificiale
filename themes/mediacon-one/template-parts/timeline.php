<?php
/**
 * Semantic timeline.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
if ( ! $items ) {
	return;
}
?>
<ol class="timeline">
<?php
foreach ( $items as $item ) :
	?>
	<li><span class="timeline__marker" aria-hidden="true"></span><div><h3><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></h3><div><?php echo wp_kses_post( (string) ( $item['content'] ?? '' ) ); ?></div></div></li><?php endforeach; ?></ol>
