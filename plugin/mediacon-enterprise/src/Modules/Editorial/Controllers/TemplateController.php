<?php
/**
 * Editorial template controller.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial\Controllers;

use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Editorial\Services\EditorialRepository;
use Mediacon\Enterprise\Modules\Editorial\Support\ArchiveCatalog;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;

defined( 'ABSPATH' ) || exit;

/** Selects opt-in editorial templates and preserves theme fallbacks. */
final class TemplateController {

	/**
	 * Current editorial template definition.
	 *
	 * @var array<string,mixed>|null
	 */
	private ?array $current = null;

	/**
	 * Create the template integration.
	 *
	 * @param ArchiveCatalog      $catalog Archive catalog.
	 * @param EditorialRepository $repository Editorial repository.
	 * @param CardPresenter       $cards Card presenter.
	 * @param CourseRepository    $courses Formation repository.
	 * @param TeacherRepository   $teachers Teacher repository.
	 * @param Template            $template Core template renderer.
	 */
	public function __construct(
		private readonly ArchiveCatalog $catalog,
		private readonly EditorialRepository $repository,
		private readonly CardPresenter $cards,
		private readonly CourseRepository $courses,
		private readonly TeacherRepository $teachers,
		private readonly Template $template
	) {}

	/**
	 * Replace the theme template only for enabled editorial requests.
	 *
	 * @param string $original Theme template.
	 * @return string
	 */
	public function filterTemplate( string $original ): string {
		$current = $this->catalog->current();

		if ( null === $current && $this->singleEnabled() && is_singular( 'post' ) && ! $this->courses->isCurrentCourse() && ! $this->teachers->isCurrentTeacher() ) {
			$current = array(
				'key'   => 'single',
				'title' => get_the_title( get_queried_object_id() ),
				'type'  => 'single',
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
	 * Render the selected template inside the theme shell.
	 *
	 * @return void
	 */
	public function renderCurrent(): void {
		if ( null === $this->current ) {
			return;
		}

		$type = (string) $this->current['type'];
		$data = array(
			'archive'    => $this->current,
			'catalog'    => $this->catalog->rows(),
			'components' => $this,
			'cards'      => $this->cards,
			'repository' => $this->repository,
		);

		if ( 'courses' === $type ) {
			$data['query']             = $this->courses->query( 'upcoming' );
			$data['course_repository'] = $this->courses;
			$template                  = 'course-archive.php';
		} elseif ( 'single' === $type ) {
			$post_id         = get_queried_object_id();
			$data['post_id'] = $post_id;
			$data['related'] = $this->repository->related( $post_id );
			$template        = 'single.php';
		} else {
			$key            = (string) $this->current['key'];
			$data['query']  = $this->repository->query( $key );
			$data['facets'] = $this->repository->facets( $key );
			$template       = 'archive.php';
		}

		get_header();
		$this->template->renderFile( $this->path( $template ), $data );
		get_footer();
	}

	/**
	 * Render a reusable editorial component.
	 *
	 * @param string              $name Component name.
	 * @param array<string,mixed> $data Component data.
	 * @return void
	 */
	public function renderComponent( string $name, array $data = array() ): void {
		$this->template->renderFile( $this->path( 'components/' . sanitize_key( $name ) . '.php' ), $data );
	}

	/**
	 * Determine whether single-article replacement is enabled.
	 *
	 * @return bool
	 */
	private function singleEnabled(): bool {
		return ! empty( $this->cards->general()['single_template'] );
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
