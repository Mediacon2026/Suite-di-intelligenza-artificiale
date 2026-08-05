<?php
/**
 * Badge component.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$label = isset( $args['label'] ) ? (string) $args['label'] : '';
if ( '' !== $label ) :
	?><span class="badge"><?php echo esc_html( $label ); ?></span><?php endif; ?>
