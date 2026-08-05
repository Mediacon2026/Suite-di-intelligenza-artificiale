<?php
/**
 * Read-only WordPress site inventory.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Enterprise;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

defined( 'ABSPATH' ) || exit;

/** Discovers runtime architecture without changing content or configuration. */
final class SiteInventory {

	/**
	 * Cached plugin inventory.
	 *
	 * @var array<string,array<string,mixed>>|null
	 */
	private ?array $plugins = null;

	/** Return the complete, non-sensitive inventory. */
	public function report(): array {
		return array(
			'pages'         => $this->pages(),
			'plugins'       => $this->plugins(),
			'templates'     => $this->templateFiles(),
			'shortcodes'    => $this->shortcodes(),
			'widgets'       => $this->widgets(),
			'post_types'    => function_exists( 'get_post_types' ) ? get_post_types( array(), 'names' ) : array(),
			'taxonomies'    => function_exists( 'get_taxonomies' ) ? get_taxonomies( array(), 'names' ) : array(),
			'hooks'         => $this->hooks(),
			'rest_routes'   => $this->restRoutes(),
			'options'       => $this->optionNames(),
			'admin_menus'   => $this->adminMenus(),
			'nav_locations' => function_exists( 'get_registered_nav_menus' ) ? get_registered_nav_menus() : array(),
			'nav_menus'     => function_exists( 'wp_get_nav_menus' ) ? wp_get_nav_menus() : array(),
		);
	}

	/** Return every WordPress page with architectural metadata. */
	public function pages(): array {
		if ( ! function_exists( 'get_pages' ) ) {
			return array();
		}
		$rows = array();
		foreach ( get_pages(
			array(
				'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'sort_column' => 'ID',
			)
		) as $page ) {
			$rows[] = array(
				'id'       => (int) $page->ID,
				'title'    => (string) $page->post_title,
				'slug'     => (string) $page->post_name,
				'status'   => (string) $page->post_status,
				'template' => (string) ( get_page_template_slug( $page->ID ) ? get_page_template_slug( $page->ID ) : 'default' ),
				'url'      => (string) get_permalink( $page->ID ),
			);
		}
		return $rows;
	}

	/** Return metadata and discovered contracts for installed Mediacon/legacy plugins. */
	public function plugins(): array {
		if ( null !== $this->plugins ) {
			return $this->plugins;
		}
		if ( ! function_exists( 'get_plugins' ) ) {
			$file = ABSPATH . 'wp-admin/includes/plugin.php';
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
		if ( ! function_exists( 'get_plugins' ) ) {
			return array();
		}

		$this->plugins = array();
		foreach ( get_plugins() as $file => $data ) {
			if ( plugin_basename( MEDIACON_ENTERPRISE_FILE ) === $file ) {
				continue;
			}
			$source    = $this->pluginSource( $file );
			$is_legacy = str_contains( strtolower( $file . ' ' . (string) ( $data['Name'] ?? '' ) . ' ' . $source ), 'mediacon' );
			if ( ! $is_legacy ) {
				continue;
			}
			$hooks     = $this->matches( $source, '/(?:add_action|add_filter)\s*\(\s*[\'\"]([^\'\"]+)/' );
			$functions = $this->missingContracts( $source, 'function_exists', 'function_exists' );
			$classes   = $this->missingContracts( $source, 'class_exists', 'class_exists' );
			$constants = $this->missingContracts( $source, 'defined', 'defined' );
			$templates = $this->matches( $source, '/[\'\"]([^\'\"]*(?:template|single|archive|page)[^\'\"]*\.php)[\'\"]/i' );
			$managed   = array();
			foreach ( $this->pages() as $page ) {
				if ( '' !== $page['slug'] && str_contains( $source, (string) $page['slug'] ) ) {
					$managed[] = $page;
				}
			}
			$this->plugins[ $file ] = array(
				'file'              => $file,
				'name'              => sanitize_text_field( (string) ( $data['Name'] ?? $file ) ),
				'version'           => sanitize_text_field( (string) ( $data['Version'] ?? '' ) ),
				'active'            => function_exists( 'is_plugin_active' ) ? is_plugin_active( $file ) : in_array( $file, (array) get_option( 'active_plugins', array() ), true ),
				'compatible'        => array() === $functions && array() === $classes && array() === $constants,
				'hooks'             => $hooks,
				'missing_functions' => $functions,
				'missing_constants' => $constants,
				'missing_classes'   => $classes,
				'templates'         => $templates,
				'pages'             => $managed,
			);
		}
		ksort( $this->plugins );
		return $this->plugins;
	}

	/** Map page IDs to the most specific detected legacy plugin. */
	public function legacyPageAssociations(): array {
		$result = array();
		foreach ( $this->plugins() as $plugin ) {
			foreach ( $plugin['pages'] as $page ) {
				$result[ (int) $page['id'] ] = (string) $plugin['name'];
			}
		}
		return $result;
	}

	/**
	 * Read plugin PHP source with a bounded per-file size.
	 *
	 * @param string $plugin_file Plugin basename.
	 */
	private function pluginSource( string $plugin_file ): string {
		$root = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
		if ( '.' === dirname( $plugin_file ) ) {
			$root = WP_PLUGIN_DIR . '/' . $plugin_file;
		}
		$files = is_dir( $root ) ? $this->phpFiles( $root, 500 ) : array( $root );
		$text  = '';
		foreach ( $files as $file ) {
			if ( is_readable( $file ) && filesize( $file ) <= 1024 * 1024 ) {
				$chunk = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local read-only inventory.
				$text .= false === $chunk ? '' : "\n" . $chunk;
			}
		}
		return $text;
	}

	/**
	 * Find unavailable guarded contracts.
	 *
	 * @param string $source Plugin source.
	 * @param string $marker Guard function.
	 * @param string $checker Runtime checker.
	 * @return array<int,string>
	 */
	private function missingContracts( string $source, string $marker, string $checker ): array {
		$names   = $this->matches( $source, '/\b' . preg_quote( $marker, '/' ) . '\s*\(\s*[\'\"]([^\'\"]+)/' );
		$missing = array();
		foreach ( $names as $name ) {
			$available = 'defined' === $checker ? defined( $name ) : $checker( $name );
			if ( ! $available ) {
				$missing[] = $name;
			}
		}
		return $missing;
	}

	/**
	 * Return unique regex captures.
	 *
	 * @param string $source Source text.
	 * @param string $pattern Regular expression.
	 * @return array<int,string>
	 */
	private function matches( string $source, string $pattern ): array {
		preg_match_all( $pattern, $source, $matches );
		return array_values( array_unique( array_map( 'sanitize_text_field', $matches[1] ?? array() ) ) );
	}

	/** Return registered shortcode tags. @return array<int,string> */
	private function shortcodes(): array {
		global $shortcode_tags;
		$tags = is_array( $shortcode_tags ) ? array_keys( $shortcode_tags ) : array();
		sort( $tags );
		return $tags;
	}

	/** Return registered widget classes. @return array<int,string> */
	private function widgets(): array {
		global $wp_widget_factory;
		$widgets = is_object( $wp_widget_factory ) && isset( $wp_widget_factory->widgets ) ? array_keys( (array) $wp_widget_factory->widgets ) : array();
		sort( $widgets );
		return $widgets;
	}

	/** Return hook names and callback counts. @return array<string,int> */
	private function hooks(): array {
		global $wp_filter;
		$result = array();
		foreach ( is_array( $wp_filter ) ? $wp_filter : array() as $name => $hook ) {
			$callbacks       = is_object( $hook ) && isset( $hook->callbacks ) ? (array) $hook->callbacks : array();
			$result[ $name ] = array_sum( array_map( 'count', $callbacks ) );
		}
		ksort( $result );
		return $result;
	}

	/** Return routes already registered on the REST server. @return array<int,string> */
	private function restRoutes(): array {
		global $wp_rest_server;
		if ( ! is_object( $wp_rest_server ) || ! method_exists( $wp_rest_server, 'get_routes' ) ) {
			return array();
		}
		$routes = array_keys( $wp_rest_server->get_routes() );
		sort( $routes );
		return $routes;
	}

	/** Return option names only; values can contain secrets and are never exported. */
	private function optionNames(): array {
		global $wpdb;
		if ( ! isset( $wpdb->options ) ) {
			return array();
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Read-only architecture inventory; values are deliberately excluded.
		$names = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} ORDER BY option_name" );
		return array_values( array_map( 'sanitize_text_field', is_array( $names ) ? $names : array() ) );
	}

	/** Return the current administrative menu globals. @return array<string,mixed> */
	private function adminMenus(): array {
		global $menu, $submenu;
		return array(
			'menu'    => is_array( $menu ) ? array_values( $menu ) : array(),
			'submenu' => is_array( $submenu ) ? $submenu : array(),
		);
	}

	/** Return template-like files below plugin and theme roots. @return array<int,string> */
	private function templateFiles(): array {
		$roots = array( WP_PLUGIN_DIR );
		if ( function_exists( 'get_theme_root' ) ) {
			$roots[] = get_theme_root();
		}
		if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
			$roots[] = WPMU_PLUGIN_DIR;
		}
		$files = array();
		foreach ( array_unique( $roots ) as $root ) {
			foreach ( $this->phpFiles( $root, 5000 ) as $file ) {
				$name = strtolower( basename( $file ) );
				if ( str_contains( strtolower( $file ), 'template' ) || preg_match( '/^(?:front-page|home|index|page|single|archive|category|taxonomy|search)(?:-|\.)/', $name ) ) {
					$files[] = str_replace( ABSPATH, '', $file );
				}
			}
		}
		sort( $files );
		return array_values( array_unique( $files ) );
	}

	/**
	 * Enumerate bounded PHP files below a root.
	 *
	 * @param string $root Root directory.
	 * @param int    $limit Maximum file count.
	 * @return array<int,string>
	 */
	private function phpFiles( string $root, int $limit ): array {
		if ( ! is_dir( $root ) ) {
			return array();
		}
		$result = array();
		try {
			$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, RecursiveDirectoryIterator::SKIP_DOTS ) );
			foreach ( $iterator as $file ) {
				if ( $file instanceof SplFileInfo && $file->isFile() && 'php' === strtolower( $file->getExtension() ) ) {
					$result[] = $file->getPathname();
					if ( count( $result ) >= $limit ) {
						break;
					}
				}
			}
		} catch ( \UnexpectedValueException ) {
			return $result;
		}
		return $result;
	}
}
