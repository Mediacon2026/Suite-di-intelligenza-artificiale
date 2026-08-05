<?php
/**
 * Plugin uninstall handler.
 *
 * @package MediaconEnterprise
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$mediacon_enterprise_settings = get_option( 'mediacon_enterprise_settings', array() );
$mediacon_enterprise_opt_in   = is_array( $mediacon_enterprise_settings ) && ! empty( $mediacon_enterprise_settings['delete_on_uninstall'] );

if ( ! defined( 'MEDIACON_ENTERPRISE_REMOVE_DATA' ) || true !== MEDIACON_ENTERPRISE_REMOVE_DATA || ! $mediacon_enterprise_opt_in ) {
	return;
}

delete_option( 'mediacon_enterprise_settings' );
delete_option( 'mediacon_enterprise_version' );
delete_option( 'mediacon_enterprise_cache_search' );
delete_option( 'mediacon_enterprise_cache_search_rate' );
delete_option( 'mediacon_enterprise_runtime_diagnostics' );

if ( is_multisite() ) {
	delete_site_option( 'mediacon_enterprise_settings' );
	delete_site_option( 'mediacon_enterprise_version' );
	delete_site_option( 'mediacon_enterprise_cache_search' );
	delete_site_option( 'mediacon_enterprise_cache_search_rate' );
	delete_site_option( 'mediacon_enterprise_runtime_diagnostics' );
}
