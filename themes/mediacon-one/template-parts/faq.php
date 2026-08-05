<?php
/**
 * FAQ accordion fed by caller-provided existing content.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
if ( ! $items ) {
	return;
}
?>
<div class="faq" data-accordion>
<?php
foreach ( $items as $index => $item ) :
	$panel_id = 'faq-panel-' . wp_unique_id( (string) $index );
	?>
	<section class="faq__item"><h2><button type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>"><?php echo esc_html( (string) ( $item['question'] ?? '' ) ); ?><span aria-hidden="true">+</span></button></h2><div id="<?php echo esc_attr( $panel_id ); ?>" class="faq__answer" hidden><?php echo wp_kses_post( (string) ( $item['answer'] ?? '' ) ); ?></div></section><?php endforeach; ?></div>
