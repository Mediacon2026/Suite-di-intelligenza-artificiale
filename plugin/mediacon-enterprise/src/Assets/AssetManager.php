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
}
