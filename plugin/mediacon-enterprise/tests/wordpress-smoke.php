<?php
/**
 * Standalone WordPress fallback and reversibility smoke test.
 *
 * @package MediaconEnterprise
 */

require __DIR__ . '/bootstrap.php';

$settings   = new Mediacon\Enterprise\Core\SettingsManager();
$governance = new Mediacon\Enterprise\Enterprise\PageGovernance( $settings );

foreach ( array( 'wordpress', 'enterprise', 'legacy', 'wordpress' ) as $mode ) {
	if ( ! $governance->setMode( 'formation', 'formation', $mode ) || $mode !== $governance->mode( 'formation', 'formation' ) ) {
		fwrite( STDERR, "Ownership mode did not round-trip.\n" );
		exit( 1 );
	}
}

$forbidden = array( 'wp_insert_post(', 'wp_update_post(', 'wp_delete_post(', 'wp_create_nav_menu(', 'wp_delete_nav_menu(' );
$iterator  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( dirname( __DIR__ ) . '/src', FilesystemIterator::SKIP_DOTS ) );
foreach ( $iterator as $file ) {
	if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) {
		continue;
	}
	$source = file_get_contents( $file->getPathname() );
	foreach ( $forbidden as $call ) {
		if ( false !== strpos( (string) $source, $call ) ) {
			fwrite( STDERR, "Forbidden content or URL mutation found: {$call}\n" );
			exit( 1 );
		}
	}
}

fwrite( STDOUT, "WordPress fallback and reversibility smoke completed.\n" );
