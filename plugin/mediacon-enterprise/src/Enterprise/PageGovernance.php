<?php
/**
 * Reversible page and archive governance.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Enterprise;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/** Stores the selected owner without changing content, slugs, or URLs. */
final class PageGovernance {

	public const WORDPRESS  = 'wordpress';
	public const ENTERPRISE = 'enterprise';
	public const LEGACY     = 'legacy';

	/** Supported resource keys. @var array<string,array<int,string>> */
	private const RESOURCES = array(
		'mediation' => array( 'how-it-works', 'costs', 'civil-commercial', 'court-referred', 'online', 'application', 'participation', 'faq', 'legislation', 'case-law' ),
		'formation' => array( 'formation', 'base-course', 'advanced', 'renewal', 'calendar', 'teachers', 'faq', 'registration', 'upcoming', 'insights' ),
		'editorial' => array( 'blog', 'jurisprudence', 'legislation', 'insights', 'categories', 'courses', 'search', 'single' ),
	);

	/**
	 * Create the governance service.
	 *
	 * @param SettingsManager $settings Settings service.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/** Return supported resources. @return array<string,array<int,string>> */
	public function resources(): array {
		return self::RESOURCES;
	}

	/**
	 * Resolve a stored mode, retaining the pre-1.0 flag as fallback.
	 *
	 * @param string $module Module identifier.
	 * @param string $resource_key Resource key.
	 * @param bool   $enterprise_enabled Previous template flag.
	 */
	public function mode( string $module, string $resource_key, bool $enterprise_enabled = false ): string {
		$map  = $this->settings->get( 'page_governance', array() );
		$mode = is_array( $map ) ? ( $map[ $module ][ $resource_key ] ?? '' ) : '';
		if ( in_array( $mode, array( self::WORDPRESS, self::ENTERPRISE, self::LEGACY ), true ) ) {
			return $mode;
		}
		return $enterprise_enabled ? self::ENTERPRISE : self::WORDPRESS;
	}

	/**
	 * Update one mode and synchronize the existing template flag.
	 *
	 * @param string $module Module identifier.
	 * @param string $resource_key Resource key.
	 * @param string $mode Ownership mode.
	 */
	public function setMode( string $module, string $resource_key, string $mode ): bool {
		$module       = sanitize_key( $module );
		$resource_key = sanitize_key( $resource_key );
		$mode         = sanitize_key( $mode );
		if ( ! isset( self::RESOURCES[ $module ] ) || ! in_array( $resource_key, self::RESOURCES[ $module ], true ) || ! in_array( $mode, array( self::WORDPRESS, self::ENTERPRISE, self::LEGACY ), true ) ) {
			return false;
		}

		$map                             = $this->settings->get( 'page_governance', array() );
		$map                             = is_array( $map ) ? $map : array();
		$map[ $module ][ $resource_key ] = $mode;
		$this->settings->set( 'page_governance', $map );

		$module_settings = $this->settings->get( $module, array() );
		if ( is_array( $module_settings ) ) {
			$module_settings['templates']                  = isset( $module_settings['templates'] ) && is_array( $module_settings['templates'] ) ? $module_settings['templates'] : array();
			$module_settings['templates'][ $resource_key ] = self::ENTERPRISE === $mode;
			if ( 'editorial' === $module && 'single' === $resource_key ) {
				$module_settings['general']                    = isset( $module_settings['general'] ) && is_array( $module_settings['general'] ) ? $module_settings['general'] : array();
				$module_settings['general']['single_template'] = self::ENTERPRISE === $mode;
			}
			$this->settings->set( $module, $module_settings );
		}
		return true;
	}

	/**
	 * Add governance and WordPress metadata to catalog rows.
	 *
	 * @param string $module Module identifier.
	 * @param array  $rows Catalog rows.
	 * @param array  $legacy_associations Legacy page map.
	 */
	public function decorate( string $module, array $rows, array $legacy_associations = array() ): array {
		foreach ( $rows as $key => &$row ) {
			$page_id                   = absint( $row['page_id'] ?? 0 );
			$post                      = $page_id > 0 ? get_post( $page_id ) : null;
			$mode                      = $this->mode( $module, (string) $key, ! empty( $row['enabled'] ) );
			$row['mode']               = $mode;
			$row['enabled']            = self::ENTERPRISE === $mode;
			$row['id']                 = $page_id;
			$row['slug']               = $post instanceof \WP_Post ? (string) $post->post_name : (string) ( $row['slug'] ?? '' );
			$row['status']             = $post instanceof \WP_Post ? (string) $post->post_status : __( 'Non rilevata', 'mediacon-enterprise' );
			$row['wordpress_template'] = $page_id > 0 ? (string) get_page_template_slug( $page_id ) : '';
			$row['wordpress_template'] = '' !== $row['wordpress_template'] ? $row['wordpress_template'] : __( 'Predefinito del tema', 'mediacon-enterprise' );
			$row['enterprise_module']  = $module;
			$row['legacy_plugin']      = $legacy_associations[ $page_id ] ?? __( 'Nessuno rilevato', 'mediacon-enterprise' );
		}
		unset( $row );
		return $rows;
	}

	/** Return all stored modes for snapshots and verification. */
	public function allModes(): array {
		$map = $this->settings->get( 'page_governance', array() );
		return is_array( $map ) ? $map : array();
	}

	/**
	 * Restore a previously sanitized mode map.
	 *
	 * @param array $modes Mode map.
	 */
	public function restore( array $modes ): void {
		foreach ( self::RESOURCES as $module => $resources ) {
			foreach ( $resources as $resource ) {
				if ( isset( $modes[ $module ][ $resource ] ) ) {
					$this->setMode( $module, $resource, (string) $modes[ $module ][ $resource ] );
				}
			}
		}
	}
}
