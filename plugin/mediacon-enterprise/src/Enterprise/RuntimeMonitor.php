<?php
/**
 * Non-invasive runtime warning and fatal monitor.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Enterprise;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/** Records bounded diagnostics while preserving the native PHP error flow. */
final class RuntimeMonitor {

	private const OPTION = 'mediacon_enterprise_runtime_diagnostics';

	/**
	 * Previously registered PHP handler.
	 *
	 * @var callable|null
	 */
	private $previous_handler = null;

	/**
	 * Create the runtime monitor.
	 *
	 * @param SettingsManager $settings Settings service.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/** Register observers only when administrators enabled diagnostics. */
	public function register(): void {
		if ( ! $this->settings->get( 'diagnostics_enabled', true ) ) {
			return;
		}
		$this->previous_handler = set_error_handler( array( $this, 'captureError' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Required compatibility monitor; previous handler is preserved.
		register_shutdown_function( array( $this, 'captureShutdown' ) );
	}

	/**
	 * Capture an error and preserve the preceding handler.
	 *
	 * @param int    $severity Error level.
	 * @param string $message Safe error message.
	 * @param string $file Source file.
	 * @param int    $line Source line.
	 */
	public function captureError( int $severity, string $message, string $file, int $line ): bool {
		if ( 0 === ( error_reporting() & $severity ) ) { // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting,WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting -- Reads current reporting mask; does not change it.
			return $this->delegate( $severity, $message, $file, $line );
		}
		$this->append( $this->severity( $severity ), $message, $file, $line, false );
		return $this->delegate( $severity, $message, $file, $line );
	}

	/** Capture the terminal fatal family during shutdown. */
	public function captureShutdown(): void {
		$error = error_get_last();
		if ( ! is_array( $error ) || ! in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
			return;
		}
		$this->append( 'critical', (string) $error['message'], (string) $error['file'], (int) $error['line'], true );
	}

	/** Return recorded diagnostics without exposing absolute paths. */
	public function events(): array {
		$events = get_option( self::OPTION, array() );
		return is_array( $events ) ? $events : array();
	}

	/**
	 * Store one bounded event.
	 *
	 * @param string $severity Severity.
	 * @param string $message Message.
	 * @param string $file File.
	 * @param int    $line Line.
	 * @param bool   $fatal Fatal flag.
	 */
	private function append( string $severity, string $message, string $file, int $line, bool $fatal ): void {
		$events   = $this->events();
		$events[] = array(
			'time'     => gmdate( 'c' ),
			'severity' => $severity,
			'message'  => sanitize_text_field( wp_strip_all_tags( $message ) ),
			'file'     => basename( $file ),
			'line'     => max( 0, $line ),
			'fatal'    => $fatal,
		);
		update_option( self::OPTION, array_slice( $events, -100 ), false );
	}

	/**
	 * Normalize an error level.
	 *
	 * @param int $severity PHP error level.
	 */
	private function severity( int $severity ): string {
		if ( in_array( $severity, array( E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING ), true ) ) {
			return 'warning';
		}
		if ( in_array( $severity, array( E_DEPRECATED, E_USER_DEPRECATED, E_NOTICE, E_USER_NOTICE ), true ) ) {
			return 'notice';
		}
		return 'error';
	}

	/**
	 * Preserve any error handler registered before Enterprise.
	 *
	 * @param int    $severity Error level.
	 * @param string $message Error message.
	 * @param string $file Source file.
	 * @param int    $line Source line.
	 */
	private function delegate( int $severity, string $message, string $file, int $line ): bool {
		if ( is_callable( $this->previous_handler ) ) {
			return (bool) call_user_func( $this->previous_handler, $severity, $message, $file, $line );
		}
		return false;
	}
}
