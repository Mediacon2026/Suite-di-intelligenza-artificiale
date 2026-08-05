<?php
/**
 * Design Core compatibility module.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;

/** Registers the minimal documented compatibility surface. */
final class CompatibilityModule implements Module {

	/**
	 * Whether the compatibility module registered successfully.
	 *
	 * @var bool
	 */
	private bool $active = false;

	/**
	 * Hook publisher used after registration.
	 *
	 * @var LegacyHookBridge|null
	 */
	private ?LegacyHookBridge $hook_bridge = null;

	/**
	 * Create the compatibility module.
	 *
	 * @param LegacyContractRegistry $registry Contract registry.
	 */
	public function __construct( private readonly LegacyContractRegistry $registry ) {}

	/** Return the stable module identifier. @return string */
	public function id(): string {
		return 'compatibility';
	}

	/**
	 * Register compatibility services and hooks.
	 *
	 * @param Container   $container Core container.
	 * @param HookManager $hooks     Hook manager.
	 * @return void
	 */
	public function register( Container $container, HookManager $hooks ): void {
		if ( ! apply_filters( 'mediacon_enterprise_compatibility_enabled', true ) ) {
			return;
		}

		$this->active = true;
		$this->registerConstants();
		require_once __DIR__ . '/functions.php';
		$functions = $container->get( LegacyFunctionBridge::class );
		$functions->activate( $container );
		$pages = $container->get( LegacyPageAdapter::class );
		$hooks->filter( 'template_include', array( $pages, 'filterTemplate' ), 99 );
		$assets = $container->get( LegacyAssetBridge::class );
		$hooks->action( 'init', array( $assets, 'registerDefaults' ), 2, 0 );
		$this->hook_bridge = $container->get( LegacyHookBridge::class );
		$this->hook_bridge->register( $container, $hooks );
		$admin = $container->get( CompatibilityAdminPage::class );
		$hooks->action( 'admin_menu', array( $admin, 'registerMenu' ), 50, 0 );
		$hooks->action( 'admin_post_mediacon_enterprise_download_bridge', array( $admin, 'downloadBridge' ), 10, 0 );
		$hooks->action( 'admin_post_mediacon_enterprise_legacy_migration', array( $admin, 'migrationAction' ), 10, 0 );
		$hooks->action( 'admin_notices', array( $container->get( LegacyDiagnostics::class ), 'renderNotice' ), 10, 0 );
		$this->registerFunctions();
		$this->registry->classAvailable( self::class );
		$this->registry->classAvailable( LegacyContractRegistry::class );
		$this->registry->classAvailable( LegacyClassBridge::class );
	}

	/** Announce that the compatibility layer is ready. @return void */
	public function boot(): void {
		if ( $this->active ) {
			$this->hook_bridge?->announceLoaded();
			do_action( 'mediacon_enterprise_compatibility_ready', $this->registry );
		}
	}

	/** Determine whether the compatibility module is active. @return bool */
	public function isActive(): bool {
		return $this->active;
	}

	/** Define guarded compatibility constants. @return void */
	private function registerConstants(): void {
		$this->registry->defineConstant( 'MEDIACON_DESIGN_CORE_VERSION', MEDIACON_ENTERPRISE_VERSION );
		$this->registry->defineConstant( 'MEDIACON_DESIGN_CORE_FILE', MEDIACON_ENTERPRISE_FILE );
		$this->registry->defineConstant( 'MEDIACON_DESIGN_CORE_PATH', MEDIACON_ENTERPRISE_PATH );
		$this->registry->defineConstant( 'MEDIACON_DESIGN_CORE_URL', MEDIACON_ENTERPRISE_URL );
		$this->registry->defineConstant( 'MDC_VERSION', MEDIACON_ENTERPRISE_VERSION );
		$this->registry->defineConstant( 'MDC_FILE', MEDIACON_ENTERPRISE_FILE );
		$this->registry->defineConstant( 'MDC_PATH', MEDIACON_ENTERPRISE_PATH );
		$this->registry->defineConstant( 'MDC_URL', MEDIACON_ENTERPRISE_URL );
	}

	/** Record the guarded global functions. @return void */
	private function registerFunctions(): void {
		foreach ( array(
			'mediacon_design_core',
			'mediacon_design_core_path',
			'mediacon_design_core_url',
			'mediacon_design_core_register_style',
			'mediacon_design_core_register_script',
			'mediacon_design_core_enqueue_style',
			'mediacon_design_core_enqueue_script',
			'mediacon_design_core_register_page',
			'mediacon_design_core_page_id',
			'mediacon_design_core_template',
			'mediacon_design_core_component',
			'mediacon_design_core_setting',
			'mediacon_design_core_logo_url',
			'mdc_register_page',
			'mdc_render_page_hero',
			'mdc_render_footer',
		) as $function ) {
			$this->registry->functionAvailable( $function );
		}
	}
}
