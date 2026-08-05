<?php
/**
 * Plugin settings manager.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

/**
 * Provides typed access to a single plugin option.
 */
final class SettingsManager {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private const OPTION = 'mediacon_enterprise_settings';

	/**
	 * Default settings.
	 *
	 * @var array<string,mixed>
	 */
	private const DEFAULTS = array(
		'enabled_modules'     => array( 'mediation', 'formation' ),
		'delete_on_uninstall' => false,
	);

	/**
	 * Register the WordPress setting.
	 *
	 * @return void
	 */
	public function register(): void {
		register_setting(
			'mediacon_enterprise',
			self::OPTION,
			array(
				'type'              => 'object',
				'default'           => self::DEFAULTS,
				'sanitize_callback' => array( $this, 'sanitize' ),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Retrieve a setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $fallback Fallback value.
	 * @return mixed
	 */
	public function get( string $key, mixed $fallback = null ): mixed {
		$settings = get_option( self::OPTION, self::DEFAULTS );
		$settings = is_array( $settings ) ? wp_parse_args( $settings, self::DEFAULTS ) : self::DEFAULTS;

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $fallback;
	}

	/**
	 * Persist a setting.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool
	 */
	public function set( string $key, mixed $value ): bool {
		$settings         = get_option( self::OPTION, self::DEFAULTS );
		$settings         = is_array( $settings ) ? wp_parse_args( $settings, self::DEFAULTS ) : self::DEFAULTS;
		$settings[ $key ] = $value;

		return update_option( self::OPTION, $this->sanitize( $settings ) );
	}

	/**
	 * Sanitize the complete option value.
	 *
	 * @param mixed $value Submitted option value.
	 * @return array<string,mixed>
	 */
	public function sanitize( mixed $value ): array {
		$value   = is_array( $value ) ? $value : array();
		$modules = isset( $value['enabled_modules'] ) && is_array( $value['enabled_modules'] )
			? array_map( 'sanitize_key', $value['enabled_modules'] )
			: self::DEFAULTS['enabled_modules'];

		return array(
			'enabled_modules'     => array_values( array_intersect( array( 'mediation', 'formation' ), $modules ) ),
			'delete_on_uninstall' => ! empty( $value['delete_on_uninstall'] ),
		);
	}
}
