<?php
/**
 * Standalone live-migration contract tests.
 *
 * @package MediaconOne
 */

$mode  = $argv[1] ?? 'inactive';
$theme = realpath( $argv[2] ?? dirname( __DIR__, 2 ) . '/themes/mediacon-one' );
if ( false === $theme || ! in_array( $mode, array( 'active', 'inactive' ), true ) ) {
	fwrite( STDERR, "Usage: php migration-contract.php <active|inactive> [theme-directory]\n" );
	exit( 2 );
}

define( 'ABSPATH', $theme . '/wordpress/' );
define( 'MEDIACON_ONE_PATH', rtrim( $theme, '/\\' ) . '/' );
if ( 'active' === $mode ) {
	define( 'MEDIACON_ENTERPRISE_VERSION', 'test' );
	eval( 'namespace Mediacon\\Enterprise\\Core; final class Plugin {}' );
}

final class WP_Post {
	public int $ID;
	public string $post_content = '';
	public string $post_status = 'publish';

	public function __construct( int $id ) {
		$this->ID = $id;
	}
}
final class WP_Term {}

$GLOBALS['mco_current_id'] = 43;
$GLOBALS['mco_filters']    = array();
$GLOBALS['mco_content']    = '<h1>Atto di adesione</h1><p><a href="/modulo.docx">Scarica il modulo Word</a></p>';
$GLOBALS['mco_options']    = array(
	'mediacon_enterprise_settings' => array(
		'enabled_modules' => array( 'preventivo', 'search' ),
		'preventivo'      => array( 'frontend_enabled' => true, 'page_id' => 42 ),
		'search'          => array( 'frontend_enabled' => true, 'page_id' => 44 ),
	),
);

function __( string $text ): string {
	return $text;
}
function sanitize_title( string $value ): string {
	return strtolower( trim( $value ) );
}
function sanitize_key( string $value ): string {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) );
}
function absint( mixed $value ): int {
	return abs( (int) $value );
}
function get_option( string $key, mixed $fallback = false ): mixed {
	return $GLOBALS['mco_options'][ $key ] ?? $fallback;
}
function get_page_by_path( string $path ): ?WP_Post {
	$ids = array(
		'costi-della-mediazione'             => 42,
		'modello-di-adesione-alla-mediazione' => 43,
	);
	return isset( $ids[ $path ] ) ? new WP_Post( $ids[ $path ] ) : null;
}
function get_post( int $id ): WP_Post {
	return new WP_Post( $id );
}
function get_term_by(): false {
	return false;
}
function is_page(): bool {
	return true;
}
function is_front_page(): bool {
	return false;
}
function is_category(): bool {
	return false;
}
function get_queried_object(): ?WP_Term {
	return null;
}
function get_queried_object_id(): int {
	return $GLOBALS['mco_current_id'];
}
function apply_filters( string $hook, mixed $value ): mixed {
	if ( empty( $GLOBALS['mco_filters'][ $hook ] ) ) {
		return $value;
	}
	ksort( $GLOBALS['mco_filters'][ $hook ] );
	foreach ( $GLOBALS['mco_filters'][ $hook ] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$value = $callback( $value );
		}
	}
	return $value;
}
function add_filter( string $hook, callable $callback, int $priority = 10 ): void {
	$GLOBALS['mco_filters'][ $hook ][ $priority ][] = $callback;
}
function remove_filter( string $hook, callable $callback, int $priority = 10 ): void {
	if ( empty( $GLOBALS['mco_filters'][ $hook ][ $priority ] ) ) {
		return;
	}
	$GLOBALS['mco_filters'][ $hook ][ $priority ] = array_filter(
		$GLOBALS['mco_filters'][ $hook ][ $priority ],
		static fn ( callable $registered ): bool => $registered !== $callback
	);
}
function the_content(): void {
	echo apply_filters( 'the_content', $GLOBALS['mco_content'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Test stub.
}
function get_post_meta(): string {
	return '';
}
function get_the_title(): string {
	return 'Corso 2025';
}
function current_datetime(): DateTimeImmutable {
	return new DateTimeImmutable( '2026-08-06 12:00:00' );
}
function add_action(): void {}
function do_action(): void {}
function shortcode_exists(): bool {
	return false;
}
function get_search_form(): void {}
function wp_strip_all_tags( string $value ): string {
	return strip_tags( $value );
}

require $theme . '/inc/site-config.php';
require $theme . '/inc/enterprise.php';
require $theme . '/inc/content.php';
require $theme . '/inc/preflight.php';

if ( 2 !== mediacon_one_preflight_h1_count( '<h1>Uno</h1><h1 class="title">Due</h1>' ) ) {
	fwrite( STDERR, "Preflight H1 detection contract failed.\n" );
	exit( 1 );
}
if ( 2 !== mediacon_one_preflight_invalid_document_links( '<a href="#">PDF</a><a href="">Scarica modulo</a><a href="/ok.pdf">PDF valido</a>' ) ) {
	fwrite( STDERR, "Preflight document-link detection contract failed.\n" );
	exit( 1 );
}

$expected_enterprise = 'active' === $mode;
if ( $expected_enterprise !== mediacon_one_enterprise_active() ) {
	fwrite( STDERR, "Enterprise detection contract failed.\n" );
	exit( 1 );
}
if ( $expected_enterprise !== mediacon_one_preventivo_enabled() ) {
	fwrite( STDERR, "Preventivo enabled/disabled contract failed.\n" );
	exit( 1 );
}

$GLOBALS['mco_current_id'] = 43;
$adhesion_template         = mediacon_one_enforce_rendering_source( 'enterprise-static.php' );
if ( MEDIACON_ONE_PATH . 'page.php' !== $adhesion_template ) {
	fwrite( STDERR, "Adesione must always use the WordPress content template.\n" );
	exit( 1 );
}

ob_start();
mediacon_one_the_content();
$adhesion = (string) ob_get_clean();
if ( str_contains( strtolower( $adhesion ), '<h1' ) || ! str_contains( $adhesion, '<h2>Atto di adesione</h2>' ) || ! str_contains( $adhesion, 'href="/modulo.docx"' ) ) {
	fwrite( STDERR, "Adesione the_content, one-H1, or document-link contract failed.\n" );
	exit( 1 );
}

$GLOBALS['mco_current_id'] = 42;
$costs_template            = mediacon_one_enforce_rendering_source( 'enterprise-preventivo.php' );
if ( $expected_enterprise && 'enterprise-preventivo.php' !== $costs_template ) {
	fwrite( STDERR, "Enabled Preventivo must own the Costi template.\n" );
	exit( 1 );
}
if ( ! $expected_enterprise && MEDIACON_ONE_PATH . 'page.php' !== $costs_template ) {
	fwrite( STDERR, "Disabled Preventivo must use the WordPress Costi fallback.\n" );
	exit( 1 );
}
$legacy = '[mediacon_calculator]legacy[/mediacon_calculator]<p>Tariffe WordPress</p>';
$costs  = mediacon_one_suppress_legacy_calculator( $legacy );
if ( $expected_enterprise && $legacy !== $costs ) {
	fwrite( STDERR, "Enterprise Preventivo must retain control when enabled.\n" );
	exit( 1 );
}
if ( ! $expected_enterprise && ( str_contains( $costs, 'legacy' ) || ! str_contains( $costs, 'Tariffe WordPress' ) ) ) {
	fwrite( STDERR, "Costi fallback or legacy calculator suppression contract failed.\n" );
	exit( 1 );
}

$card_source = (string) file_get_contents( $theme . '/template-parts/card.php' );
$css_source  = (string) file_get_contents( $theme . '/assets/css/components.css' );
foreach ( array( 'editorial-card__button', 'mediacon_one_course_status', 'badge--' ) as $contract ) {
	if ( ! str_contains( $card_source, $contract ) ) {
		fwrite( STDERR, "Editorial card contract missing: {$contract}.\n" );
		exit( 1 );
	}
}
foreach ( array( '-webkit-line-clamp: 2', 'aspect-ratio: 16 / 10', '.editorial-card__button' ) as $contract ) {
	if ( ! str_contains( $css_source, $contract ) ) {
		fwrite( STDERR, "Editorial CSS contract missing: {$contract}.\n" );
		exit( 1 );
	}
}

$wizard_js = (string) file_get_contents( dirname( $theme, 2 ) . '/plugin/mediacon-enterprise/src/Modules/Preventivo/Assets/js/preventivo.js' );
if ( str_contains( $wizard_js, 'Totale generale della procedura' ) || str_contains( $wizard_js, 'Ripartizione interna' ) ) {
	fwrite( STDERR, "Preventivo public output exposes an ambiguous total or internal allocation.\n" );
	exit( 1 );
}

fwrite( STDOUT, "Migration contracts passed with Enterprise {$mode}.\n" );
