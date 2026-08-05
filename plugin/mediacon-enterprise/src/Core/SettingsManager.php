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
		'enabled_modules'     => array( 'mediation', 'formation', 'editorial', 'preventivo' ),
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
		'editorial'           => array(
			'categories' => array(),
			'templates'  => array(),
			'general'    => array(
				'title_length'    => 70,
				'excerpt_length'  => 160,
				'image_ratio'     => '16-9',
				'columns'         => 3,
				'posts_per_page'  => 9,
				'single_template' => false,
			),
		),
		'preventivo'          => array(
			'page_id'          => 0,
			'frontend_enabled' => false,
			'brackets'         => array(
				array(
					'max' => 1000,
					'fee' => 80,
				),
				array(
					'max' => 5000,
					'fee' => 160,
				),
				array(
					'max' => 10000,
					'fee' => 290,
				),
				array(
					'max' => 25000,
					'fee' => 440,
				),
				array(
					'max' => 50000,
					'fee' => 720,
				),
				array(
					'max' => 150000,
					'fee' => 1200,
				),
				array(
					'max' => 250000,
					'fee' => 1500,
				),
				array(
					'max' => 500000,
					'fee' => 2500,
				),
				array(
					'max' => 1000000,
					'fee' => 3900,
				),
				array(
					'max' => 2500000,
					'fee' => 4600,
				),
				array(
					'max' => 5000000,
					'fee' => 6500,
				),
				array(
					'max' => 0,
					'fee' => 10000,
				),
			),
			'reductions'       => array(
				'mandatory'     => 20,
				'court_ordered' => 20,
				'voluntary'     => 0,
			),
			'increases'        => array(
				'absence'               => 0,
				'first_no_agreement'    => 0,
				'first_agreement'       => 10,
				'continuation'          => 0,
				'multiple_no_agreement' => 0,
				'multiple_agreement'    => 25,
				'mediator_proposal'     => 20,
			),
			'expenses'         => array(
				'registered_letter' => 10,
				'digital_signature' => 5,
				'extra_copy'        => 2.5,
			),
			'texts'            => array(
				'explanation' => 'Simulazione informativa per singola parte, soggetta a verifica dell’organismo.',
				'disclaimer'  => 'Il risultato non sostituisce il preventivo definitivo né la normativa applicabile.',
			),
			'print'            => array(
				'header' => 'Mediacon — Organismo di Mediazione',
				'footer' => 'Simulazione economica non vincolante.',
			),
			'simulation'       => array(
				'prefix'      => 'MC-PREV',
				'next_number' => 1,
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
		if ( 'preventivo' === $key && is_array( $settings['preventivo'] ) ) {
			return wp_parse_args( $settings['preventivo'], self::DEFAULTS['preventivo'] );
		}

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
			'enabled_modules'     => array_values( array_intersect( array( 'mediation', 'formation', 'editorial', 'preventivo' ), $modules ) ),
			'delete_on_uninstall' => ! empty( $value['delete_on_uninstall'] ),
			'mediation'           => $this->sanitizeMediation( $value['mediation'] ?? array() ),
			'formation'           => $this->sanitizeFormation( $value['formation'] ?? array() ),
			'editorial'           => $this->sanitizeEditorial( $value['editorial'] ?? array() ),
			'preventivo'          => $this->sanitizePreventivo( $value['preventivo'] ?? array() ),
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

	/**
	 * Sanitize public editorial settings.
	 *
	 * @param mixed $value Editorial settings.
	 * @return array<string,mixed>
	 */
	private function sanitizeEditorial( mixed $value ): array {
		$value      = is_array( $value ) ? $value : array();
		$categories = isset( $value['categories'] ) && is_array( $value['categories'] ) ? $value['categories'] : array();
		$templates  = isset( $value['templates'] ) && is_array( $value['templates'] ) ? $value['templates'] : array();
		$general    = isset( $value['general'] ) && is_array( $value['general'] ) ? $value['general'] : array();
		$ratio      = sanitize_key( $general['image_ratio'] ?? '16-9' );

		return array(
			'categories' => array_map( 'absint', array_map( 'wp_unslash', $categories ) ),
			'templates'  => array_map( static fn ( mixed $enabled ): bool => ! empty( $enabled ), $templates ),
			'general'    => array(
				'title_length'    => min( 140, max( 30, absint( $general['title_length'] ?? 70 ) ) ),
				'excerpt_length'  => min( 320, max( 80, absint( $general['excerpt_length'] ?? 160 ) ) ),
				'image_ratio'     => in_array( $ratio, array( '16-9', '4-3', '1-1' ), true ) ? $ratio : '16-9',
				'columns'         => min( 4, max( 2, absint( $general['columns'] ?? 3 ) ) ),
				'posts_per_page'  => min( 24, max( 3, absint( $general['posts_per_page'] ?? 9 ) ) ),
				'single_template' => ! empty( $general['single_template'] ),
			),
		);
	}

	/**
	 * Sanitize public quote-calculator settings.
	 *
	 * @param mixed $value Preventivo settings.
	 * @return array<string,mixed>
	 */
	private function sanitizePreventivo( mixed $value ): array {
		$value          = is_array( $value ) ? $value : array();
		$brackets       = isset( $value['brackets'] ) && is_array( $value['brackets'] ) ? $value['brackets'] : array();
		$reductions     = isset( $value['reductions'] ) && is_array( $value['reductions'] ) ? $value['reductions'] : array();
		$increases      = isset( $value['increases'] ) && is_array( $value['increases'] ) ? $value['increases'] : array();
		$expenses       = isset( $value['expenses'] ) && is_array( $value['expenses'] ) ? $value['expenses'] : array();
		$texts          = isset( $value['texts'] ) && is_array( $value['texts'] ) ? $value['texts'] : array();
		$print          = isset( $value['print'] ) && is_array( $value['print'] ) ? $value['print'] : array();
		$simulation     = isset( $value['simulation'] ) && is_array( $value['simulation'] ) ? $value['simulation'] : array();
		$clean_brackets = array();

		foreach ( $brackets as $bracket ) {
			if ( ! is_array( $bracket ) ) {
				continue;
			}
			$clean_brackets[] = array(
				'max' => max( 0, (float) ( $bracket['max'] ?? 0 ) ),
				'fee' => max( 0, (float) ( $bracket['fee'] ?? 0 ) ),
			);
		}
		if ( array() === $clean_brackets ) {
			$clean_brackets = self::DEFAULTS['preventivo']['brackets'];
		}

		return array(
			'page_id'          => absint( $value['page_id'] ?? 0 ),
			'frontend_enabled' => ! empty( $value['frontend_enabled'] ),
			'brackets'         => $clean_brackets,
			'reductions'       => wp_parse_args( array_map( static fn ( mixed $rate ): float => min( 100, max( 0, (float) $rate ) ), $reductions ), self::DEFAULTS['preventivo']['reductions'] ),
			'increases'        => wp_parse_args( array_map( static fn ( mixed $rate ): float => min( 300, max( 0, (float) $rate ) ), $increases ), self::DEFAULTS['preventivo']['increases'] ),
			'expenses'         => wp_parse_args( array_map( static fn ( mixed $amount ): float => max( 0, (float) $amount ), $expenses ), self::DEFAULTS['preventivo']['expenses'] ),
			'texts'            => wp_parse_args( array_map( 'sanitize_textarea_field', $texts ), self::DEFAULTS['preventivo']['texts'] ),
			'print'            => wp_parse_args( array_map( 'sanitize_text_field', $print ), self::DEFAULTS['preventivo']['print'] ),
			'simulation'       => array(
				'prefix'      => sanitize_key( $simulation['prefix'] ?? 'MC-PREV' ),
				'next_number' => max( 1, absint( $simulation['next_number'] ?? 1 ) ),
			),
		);
	}
}
