<?php
/**
 * Optional sidebar.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
if ( is_active_sidebar( 'sidebar-1' ) ) :
	?><aside class="sidebar" aria-label="<?php esc_attr_e( 'Contenuti correlati', 'mediacon-one' ); ?>"><?php dynamic_sidebar( 'sidebar-1' ); ?></aside><?php endif; ?>
