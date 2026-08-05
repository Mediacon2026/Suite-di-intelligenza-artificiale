<?php
/**
 * Plugin asset manager.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Assets;

/**
 * Registers and enqueues versioned plugin assets.
 */
final class AssetManager {

	/**
	 * Register a versioned plugin stylesheet.
	 *
	 * @param string            $handle        Stylesheet handle.
	 * @param string            $relative_path Path relative to the plugin root.
	 * @param array<int,string> $dependencies  Dependency handles.
	 * @return void
	 */
	public function registerStyle( string $handle, string $relative_path, array $dependencies = array() ): void {
		wp_register_style(
			sanitize_key( $handle ),
			MEDIACON_ENTERPRISE_URL . ltrim( $relative_path, '/\\' ),
			$dependencies,
			MEDIACON_ENTERPRISE_VERSION
		);
	}

	/**
	 * Register a versioned plugin script.
	 *
	 * @param string            $handle        Script handle.
	 * @param string            $relative_path Path relative to the plugin root.
	 * @param array<int,string> $dependencies  Dependency handles.
	 * @param bool              $in_footer     Whether to load the script in the footer.
	 * @return void
	 */
	public function registerScript(
		string $handle,
		string $relative_path,
		array $dependencies = array(),
		bool $in_footer = true
	): void {
		wp_register_script(
			sanitize_key( $handle ),
			MEDIACON_ENTERPRISE_URL . ltrim( $relative_path, '/\\' ),
			$dependencies,
			MEDIACON_ENTERPRISE_VERSION,
			$in_footer
		);
	}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function register(): void {
		wp_register_style(
			'mediacon-enterprise',
			MEDIACON_ENTERPRISE_URL . 'assets/css/mediacon-enterprise.css',
			array(),
			MEDIACON_ENTERPRISE_VERSION
		);

		wp_register_script(
			'mediacon-enterprise',
			MEDIACON_ENTERPRISE_URL . 'assets/js/mediacon-enterprise.js',
			array(),
			MEDIACON_ENTERPRISE_VERSION,
			true
		);
	}

	/**
	 * Enqueue frontend assets when a plugin shortcode is present.
	 *
	 * @return void
	 */
	public function enqueueFrontend(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post || ! has_shortcode( $post->post_content, 'mediacon_enterprise' ) ) {
			return;
		}

		wp_enqueue_style( 'mediacon-enterprise' );
		wp_enqueue_script( 'mediacon-enterprise' );
	}

	/**
	 * Enqueue assets on the plugin administration screen.
	 *
	 * @param string $hook_suffix Current administration page suffix.
	 * @return void
	 */
	public function enqueueAdmin( string $hook_suffix ): void {
		if ( 'toplevel_page_mediacon-enterprise' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'mediacon-enterprise' );
		wp_enqueue_script( 'mediacon-enterprise' );
	}

	/**
	 * Enqueue a previously registered stylesheet.
	 *
	 * @param string $handle Stylesheet handle.
	 * @return void
	 */
	public function enqueueStyle( string $handle ): void {
		wp_enqueue_style( sanitize_key( $handle ) );
	}

	/**
	 * Enqueue a previously registered script.
	 *
	 * @param string $handle Script handle.
	 * @return void
	 */
	public function enqueueScript( string $handle ): void {
		wp_enqueue_script( sanitize_key( $handle ) );
	}
}
