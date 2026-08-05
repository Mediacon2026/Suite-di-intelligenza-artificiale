<?php
/**
 * Formation public template controller.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Controllers;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;
use Mediacon\Enterprise\Modules\Formation\Support\Content;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Selects opt-in formation templates and preserves theme fallbacks.
 */
final class TemplateController {

	/**
	 * Current template definition.
	 *
	 * @var array<string,mixed>|null
	 */
	private ?array $current = null;

	/**
	 * Create the controller.
	 *
	 * @param PageCatalog       $catalog  Page catalog.
	 * @param Content           $content  Editable public content.
	 * @param CourseRepository  $courses  Course integration.
	 * @param TeacherRepository $teachers Teacher integration.
	 * @param SettingsManager   $settings Core settings manager.
	 * @param Template          $template Core template service.
	 */
	public function __construct(
		private readonly PageCatalog $catalog,
		private readonly Content $content,
		private readonly CourseRepository $courses,
		private readonly TeacherRepository $teachers,
		private readonly SettingsManager $settings,
		private readonly Template $template
	) {}

	/**
	 * Replace templates only for explicitly enabled content.
	 *
	 * @param string $original Theme template path.
	 * @return string
	 */
	public function filterTemplate( string $original ): string {
		$current = $this->catalog->current();

		if ( null === $current && $this->detailEnabled() && $this->courses->isCurrentCourse() ) {
			$current = array(
				'key'     => 'single-course',
				'type'    => 'course',
				'page_id' => get_queried_object_id(),
				'title'   => get_the_title( get_queried_object_id() ),
			);
		}

		if ( null === $current && $this->teacherDetailEnabled() && $this->teachers->isCurrentTeacher() ) {
			$current = array(
				'key'     => 'single-teacher',
				'type'    => 'teacher-detail',
				'page_id' => get_queried_object_id(),
				'title'   => get_the_title( get_queried_object_id() ),
			);
		}

		$file = $this->path( 'page-shell.php' );
		if ( null === $current || ! is_readable( $file ) ) {
			return $original;
		}

		$this->current = $current;

		return $file;
	}

	/**
	 * Render the selected template inside the active theme shell.
	 *
	 * @return void
	 */
	public function renderCurrent(): void {
		if ( null === $this->current ) {
			return;
		}

		$key      = (string) $this->current['key'];
		$content  = $this->content->page( $key );
		$template = match ( $this->current['type'] ) {
			'landing'      => 'landing.php',
			'course'       => 'course-detail.php',
			'archive'      => 'course-archive.php',
			'calendar'     => 'calendar.php',
			'teachers'     => 'teachers.php',
			'teacher-detail' => 'teacher-detail.php',
			'registration' => 'registration.php',
			'insights'     => 'insights.php',
			default        => 'standard-page.php',
		};
		$data = array(
			'page'         => $this->current,
			'content'      => $content,
			'catalog'      => $this->catalog->rows(),
			'faq'          => $this->content->faq(),
			'course_types' => $this->content->courseTypes(),
			'components'   => $this,
			'repository'   => $this->courses,
		);

		if ( in_array( $this->current['type'], array( 'landing', 'calendar' ), true ) ) {
			$data['query'] = $this->courses->query( 'upcoming' );
		} elseif ( 'archive' === $this->current['type'] ) {
			$data['query'] = $this->courses->query();
		} elseif ( 'teachers' === $this->current['type'] ) {
			$data['query'] = $this->teachers->query();
		} elseif ( 'insights' === $this->current['type'] ) {
			$data['query'] = $this->courses->insights();
		} elseif ( 'course' === $this->current['type'] ) {
			$data['course'] = $this->courses->data( (int) $this->current['page_id'], $content );
		} elseif ( 'teacher-detail' === $this->current['type'] ) {
			$data['teacher'] = $this->teachers->data( (int) $this->current['page_id'] );
		}

		get_header();
		$this->template->renderFile( $this->path( $template ), $data );
		get_footer();
	}

	/**
	 * Render a reusable formation component.
	 *
	 * @param string              $name Component name.
	 * @param array<string,mixed> $data Component data.
	 * @return void
	 */
	public function renderComponent( string $name, array $data = array() ): void {
		$this->template->renderFile( $this->path( 'components/' . sanitize_key( $name ) . '.php' ), $data );
	}

	/**
	 * Determine whether course-post replacement is enabled.
	 *
	 * @return bool
	 */
	private function detailEnabled(): bool {
		$formation = $this->settings->get( 'formation', array() );

		return ! empty( $formation['general']['detail_template'] );
	}

	/**
	 * Determine whether teacher-post replacement is enabled.
	 *
	 * @return bool
	 */
	private function teacherDetailEnabled(): bool {
		$formation = $this->settings->get( 'formation', array() );

		return ! empty( $formation['general']['teacher_detail_template'] );
	}

	/**
	 * Build a module template path.
	 *
	 * @param string $name Template filename.
	 * @return string
	 */
	private function path( string $name ): string {
		return dirname( __DIR__ ) . '/Templates/' . ltrim( $name, '/\\' );
	}
}
