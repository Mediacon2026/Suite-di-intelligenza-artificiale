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
		'mediation'           => array(
			'pages'     => array(),
			'templates' => array(),
		),
		'formation'           => array(
			'pages'     => array(),
			'templates' => array(),
			'general'   => array(
				'course_category'         => 'corsi',
				'teacher_category'        => 'docenti',
				'insight_category'        => 'formazione',
				'enrollment_url'          => '',
				'posts_per_page'          => 9,
				'detail_template'         => false,
				'teacher_detail_template' => false,
			),
		),
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
			'mediation'           => $this->sanitizeMediation( $value['mediation'] ?? array() ),
			'formation'           => $this->sanitizeFormation( $value['formation'] ?? array() ),
		);
	}

	/**
	 * Sanitize mediation module settings without interpreting module behavior.
	 *
	 * @param mixed $value Mediation settings.
	 * @return array{pages:array<string,int>,templates:array<string,bool>}
	 */
	private function sanitizeMediation( mixed $value ): array {
		$value     = is_array( $value ) ? $value : array();
		$pages     = isset( $value['pages'] ) && is_array( $value['pages'] ) ? $value['pages'] : array();
		$templates = isset( $value['templates'] ) && is_array( $value['templates'] ) ? $value['templates'] : array();

		return array(
			'pages'     => array_map( 'absint', array_map( 'wp_unslash', $pages ) ),
			'templates' => array_map( static fn ( mixed $enabled ): bool => ! empty( $enabled ), $templates ),
		);
	}

	/**
	 * Sanitize public formation settings.
	 *
	 * @param mixed $value Formation settings.
	 * @return array<string,mixed>
	 */
	private function sanitizeFormation( mixed $value ): array {
		$value     = is_array( $value ) ? $value : array();
		$pages     = isset( $value['pages'] ) && is_array( $value['pages'] ) ? $value['pages'] : array();
		$templates = isset( $value['templates'] ) && is_array( $value['templates'] ) ? $value['templates'] : array();
		$general   = isset( $value['general'] ) && is_array( $value['general'] ) ? $value['general'] : array();
		$per_page  = isset( $general['posts_per_page'] ) ? absint( $general['posts_per_page'] ) : 9;

		return array(
			'pages'     => array_map( 'absint', array_map( 'wp_unslash', $pages ) ),
			'templates' => array_map( static fn ( mixed $enabled ): bool => ! empty( $enabled ), $templates ),
			'general'   => array(
				'course_category'         => sanitize_title( wp_unslash( $general['course_category'] ?? 'corsi' ) ),
				'teacher_category'        => sanitize_title( wp_unslash( $general['teacher_category'] ?? 'docenti' ) ),
				'insight_category'        => sanitize_title( wp_unslash( $general['insight_category'] ?? 'formazione' ) ),
				'enrollment_url'          => esc_url_raw( wp_unslash( $general['enrollment_url'] ?? '' ) ),
				'posts_per_page'          => min( 24, max( 3, $per_page ) ),
				'detail_template'         => ! empty( $general['detail_template'] ),
				'teacher_detail_template' => ! empty( $general['teacher_detail_template'] ),
			),
		);
	}
}
