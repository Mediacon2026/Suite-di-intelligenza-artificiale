<?php
/**
 * Formation public assets.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Frontend;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Registers scoped formation assets through the Core asset manager.
 */
final class FormationAssets {

	/**
	 * Stylesheet handle.
	 *
	 * @var string
	 */
	private const STYLE = 'mediacon-enterprise-formation';

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	private const SCRIPT = 'mediacon-enterprise-formation';

	/**
	 * Create the asset integration.
	 *
	 * @param AssetManager      $assets  Core asset manager.
	 * @param PageCatalog       $catalog Page catalog.
	 * @param CourseRepository  $courses  Course integration.
	 * @param TeacherRepository $teachers Teacher integration.
	 */
	public function __construct(
		private readonly AssetManager $assets,
		private readonly PageCatalog $catalog,
		private readonly CourseRepository $courses,
		private readonly TeacherRepository $teachers
	) {}

	/** Register module assets. @return void */
	public function register(): void {
		$base = 'src/Modules/Formation/Assets/';
		$this->assets->registerStyle( self::STYLE, $base . 'css/formation.css', array( 'mediacon-enterprise' ) );
		$this->assets->registerScript( self::SCRIPT, $base . 'js/formation.js' );
	}

	/** Enqueue assets on active formation templates. @return void */
	public function enqueueFrontend(): void {
		$general        = $this->courses->general();
		$single_course  = ! empty( $general['detail_template'] ) && $this->courses->isCurrentCourse();
		$single_teacher = ! empty( $general['teacher_detail_template'] ) && $this->teachers->isCurrentTeacher();
		if ( null === $this->catalog->current() && ! $single_course && ! $single_teacher ) {
			return;
		}

		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::STYLE );
		$this->assets->enqueueScript( self::SCRIPT );
	}

	/**
	 * Enqueue module styles on its administration page.
	 *
	 * @param string $hook_suffix Current administration hook.
	 * @return void
	 */
	public function enqueueAdmin( string $hook_suffix ): void {
		if ( 'mediacon-enterprise_page_mediacon-enterprise-formation' !== $hook_suffix ) {
			return;
		}

		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::STYLE );
	}
}
