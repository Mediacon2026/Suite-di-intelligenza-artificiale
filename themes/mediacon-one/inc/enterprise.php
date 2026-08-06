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

/** Return the read-only Enterprise settings array. */
function mediacon_one_enterprise_settings(): array {
	$settings = get_option( 'mediacon_enterprise_settings', array() );
	return is_array( $settings ) ? $settings : array();
}

/**
 * Determine whether an Enterprise module is loaded by the plugin.
 *
 * @param string $module Enterprise module identifier.
 */
function mediacon_one_enterprise_module_enabled( string $module ): bool {
	if ( ! mediacon_one_enterprise_active() ) {
		return false;
	}

	$settings = mediacon_one_enterprise_settings();
	$defaults = class_exists( '\\Mediacon\\Enterprise\\Core\\SettingsManager' )
		? \Mediacon\Enterprise\Core\SettingsManager::DEFAULT_MODULES
		: array( 'mediation', 'formation', 'editorial', 'preventivo', 'search', 'compatibility' );
	$enabled  = isset( $settings['enabled_modules'] ) && is_array( $settings['enabled_modules'] ) ? $settings['enabled_modules'] : $defaults;
	return in_array( sanitize_key( $module ), array_map( 'sanitize_key', $enabled ), true );
}

/** Determine whether Preventivo owns a configured public page. */
function mediacon_one_preventivo_enabled(): bool {
	if ( ! mediacon_one_enterprise_module_enabled( 'preventivo' ) ) {
		return false;
	}

	$config = mediacon_one_enterprise_settings()['preventivo'] ?? array();
	return is_array( $config ) && ! empty( $config['frontend_enabled'] ) && 0 < absint( $config['page_id'] ?? 0 );
}

/**
 * Determine whether Preventivo owns the requested page.
 *
 * @param int $page_id Page ID, or zero to use the queried object.
 */
function mediacon_one_preventivo_owns_page( int $page_id = 0 ): bool {
	if ( ! mediacon_one_preventivo_enabled() ) {
		return false;
	}

	$config  = mediacon_one_enterprise_settings()['preventivo'] ?? array();
	$page_id = 0 < $page_id ? $page_id : get_queried_object_id();
	return is_array( $config ) && absint( $config['page_id'] ?? 0 ) === $page_id;
}

/** Determine whether Enterprise search is enabled for public rendering. */
function mediacon_one_enterprise_search_enabled(): bool {
	if ( ! mediacon_one_enterprise_module_enabled( 'search' ) ) {
		return false;
	}

	$config = mediacon_one_enterprise_settings()['search'] ?? array();
	return is_array( $config ) && ! empty( $config['frontend_enabled'] ) && 0 < absint( $config['page_id'] ?? 0 );
}

/** Render Enterprise search when registered, otherwise use WordPress search. */
function mediacon_one_render_search(): void {
	if ( mediacon_one_enterprise_search_enabled() && shortcode_exists( 'mediacon_search' ) ) {
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
