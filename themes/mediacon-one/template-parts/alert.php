<?php
/**
 * Status alert.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$message = isset( $args['message'] ) ? (string) $args['message'] : '';
if ( '' !== $message ) :
	?><div class="alert" role="status"><p><?php echo esc_html( $message ); ?></p></div><?php endif; ?>
