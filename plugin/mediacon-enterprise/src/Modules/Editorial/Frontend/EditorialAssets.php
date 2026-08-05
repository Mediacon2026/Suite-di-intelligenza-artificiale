<?php
/**
 * Editorial public assets.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial\Frontend;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Editorial\Support\ArchiveCatalog;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;

defined( 'ABSPATH' ) || exit;

/** Registers scoped editorial assets through the Core manager. */
final class EditorialAssets {

	private const STYLE  = 'mediacon-enterprise-editorial';
	private const SCRIPT = 'mediacon-enterprise-editorial';

	/**
	 * Create the asset integration.
	 *
	 * @param AssetManager      $assets Core asset manager.
	 * @param ArchiveCatalog    $catalog Archive catalog.
	 * @param CardPresenter     $cards Card settings.
	 * @param CourseRepository  $courses Formation course repository.
	 * @param TeacherRepository $teachers Formation teacher repository.
	 */
	public function __construct(
		private readonly AssetManager $assets,
		private readonly ArchiveCatalog $catalog,
		private readonly CardPresenter $cards,
		private readonly CourseRepository $courses,
		private readonly TeacherRepository $teachers
	) {}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function register(): void {
		$base = 'src/Modules/Editorial/Assets/';
		$this->assets->registerStyle( self::STYLE, $base . 'css/editorial.css', array( 'mediacon-enterprise' ) );
		$this->assets->registerScript( self::SCRIPT, $base . 'js/editorial.js' );
	}

	/**
	 * Enqueue assets only on active editorial templates.
	 *
	 * @return void
	 */
	public function enqueueFrontend(): void {
		$single = ! empty( $this->cards->general()['single_template'] ) && is_singular( 'post' ) && ! $this->courses->isCurrentCourse() && ! $this->teachers->isCurrentTeacher();
		if ( null === $this->catalog->current() && ! $single ) {
			return;
		}

		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::STYLE );
		$this->assets->enqueueScript( self::SCRIPT );
	}

	/**
	 * Enqueue assets on the Editoriale administration page.
	 *
	 * @param string $hook_suffix Current administration hook.
	 * @return void
	 */
	public function enqueueAdmin( string $hook_suffix ): void {
		if ( 'mediacon-enterprise_page_mediacon-enterprise-editorial' !== $hook_suffix ) {
			return;
		}
		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::STYLE );
	}
}
