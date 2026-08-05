<?php
/**
 * Plugin uninstall handler.
 *
 * @package MediaconEnterprise
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'MEDIACON_ENTERPRISE_REMOVE_DATA' ) || true !== MEDIACON_ENTERPRISE_REMOVE_DATA ) {
	return;
}

delete_option( 'mediacon_enterprise_settings' );
delete_option( 'mediacon_enterprise_version' );

if ( is_multisite() ) {
	delete_site_option( 'mediacon_enterprise_settings' );
	delete_site_option( 'mediacon_enterprise_version' );
}
