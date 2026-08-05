<?php
/**
 * Optional Mediacon Enterprise integration boundary.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

/** Detect Enterprise without loading or mutating the plugin. */
function mediacon_one_enterprise_active(): bool {
	return defined( 'MEDIACON_ENTERPRISE_VERSION' ) && class_exists( '\\Mediacon\\Enterprise\\Core\\Plugin' );
}

/** Render Enterprise search when registered, otherwise use WordPress search. */
function mediacon_one_render_search(): void {
	if ( mediacon_one_enterprise_active() && shortcode_exists( 'mediacon_search' ) ) {
		echo do_shortcode( '[mediacon_search]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Registered Enterprise shortcode owns escaped output.
		return;
	}
	get_search_form();
}

/** Publish a non-mutating integration point after theme setup. */
function mediacon_one_announce_enterprise_support(): void {
	do_action( 'mediacon_one_enterprise_support_ready', mediacon_one_enterprise_active() );
}
add_action( 'after_setup_theme', 'mediacon_one_announce_enterprise_support', 20 );
