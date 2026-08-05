<?php
/**
 * Search administration page.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Admin;

use Mediacon\Enterprise\Core\CacheManager;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;

defined( 'ABSPATH' ) || exit;

/**
 * Manages non-destructive public search configuration.
 */
final readonly class SearchAdminPage {

	/**
	 * Create the administration page.
	 *
	 * @param SettingsManager $settings Core settings.
	 * @param CacheManager    $cache    Core cache.
	 * @param Template        $template Template renderer.
	 */
	public function __construct( private SettingsManager $settings, private CacheManager $cache, private Template $template ) {}

	/**
	 * Register the Ricerca submenu.
	 *
	 * @return void
	 */
	public function registerMenu(): void {
		add_submenu_page( 'mediacon-enterprise', __( 'Ricerca', 'mediacon-enterprise' ), __( 'Ricerca', 'mediacon-enterprise' ), 'manage_options', 'mediacon-enterprise-search', array( $this, 'render' ) );
	}

	/**
	 * Render the protected settings form.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'mediacon-enterprise' ) );
		}
		$this->template->renderFile( dirname( __DIR__ ) . '/Templates/admin-page.php', array( 'config' => $this->settings->get( 'search', array() ) ) );
	}

	/**
	 * Validate and save search settings.
	 *
	 * @return void
	 */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to update these settings.', 'mediacon-enterprise' ) );
		}
		check_admin_referer( 'mediacon_enterprise_save_search', 'mediacon_enterprise_search_nonce' );
		$input      = isset( $_POST['search'] ) && is_array( $_POST['search'] ) ? wp_unslash( $_POST['search'] ) : array();
		$included   = is_array( $input['included'] ?? null ) ? $input['included'] : array();
		$priorities = is_array( $input['priorities'] ?? null ) ? $input['priorities'] : array();
		$synonyms   = array();
		$lines      = preg_split( '/\R/', (string) ( $input['synonyms_text'] ?? '' ) );
		foreach ( false === $lines ? array() : $lines as $line ) {
			$group = array_values( array_filter( array_map( 'trim', explode( ',', $line ) ) ) );
			if ( 1 < count( $group ) ) {
				$synonyms[] = $group;
			}
		}
		$excluded = array_values( array_filter( array_map( 'absint', explode( ',', (string) ( $input['excluded_text'] ?? '' ) ) ) ) );
		$this->settings->set(
			'search',
			array(
				'page_id'          => absint( $input['page_id'] ?? 0 ),
				'frontend_enabled' => ! empty( $input['frontend_enabled'] ),
				'included'         => $included,
				'excluded_ids'     => $excluded,
				'per_page'         => absint( $input['per_page'] ?? 9 ),
				'suggestion_limit' => absint( $input['suggestion_limit'] ?? 10 ),
				'min_chars'        => absint( $input['min_chars'] ?? 2 ),
				'max_chars'        => absint( $input['max_chars'] ?? 80 ),
				'max_candidates'   => absint( $input['max_candidates'] ?? 100 ),
				'synonyms'         => $synonyms,
				'priorities'       => $priorities,
				'autocomplete'     => ! empty( $input['autocomplete'] ),
				'cache_enabled'    => ! empty( $input['cache_enabled'] ),
				'cache_ttl'        => absint( $input['cache_ttl'] ?? 300 ),
				'rate_limit'       => absint( $input['rate_limit'] ?? 30 ),
			)
		);
		$this->cache->invalidate( 'search' );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'mediacon-enterprise-search',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
