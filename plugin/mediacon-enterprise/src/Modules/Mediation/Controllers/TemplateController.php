<?php
/**
 * Public template controller.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation\Controllers;

use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Mediation\Services\EditorialRepository;
use Mediacon\Enterprise\Modules\Mediation\Support\Content;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Selects optional mediation templates while preserving WordPress defaults.
 */
final class TemplateController {

	/**
	 * Current catalog row.
	 *
	 * @var array<string,mixed>|null
	 */
	private ?array $current = null;

	/**
	 * Create the controller.
	 *
	 * @param PageCatalog         $catalog    Page catalog.
	 * @param Content             $content    Editable module content.
	 * @param EditorialRepository $editorial Editorial integration.
	 * @param Template            $template   Core template service.
	 */
	public function __construct(
		private readonly PageCatalog $catalog,
		private readonly Content $content,
		private readonly EditorialRepository $editorial,
		private readonly Template $template
	) {}

	/**
	 * Replace the WordPress template only for explicitly enabled pages.
	 *
	 * @param string $original Original WordPress template path.
	 * @return string
	 */
	public function filterTemplate( string $original ): string {
		$current = $this->catalog->current();
		$file    = $this->path( 'page-shell.php' );

		if ( null === $current || ! is_readable( $file ) ) {
			return $original;
		}

		$this->current = $current;

		return $file;
	}

	/**
	 * Render the selected template within the active theme shell.
	 *
	 * @return void
	 */
	public function renderCurrent(): void {
		if ( null === $this->current ) {
			return;
		}

		$page_content = $this->content->page( (string) $this->current['key'] );
		$template     = match ( $this->current['type'] ) {
			'process'   => 'how-it-works.php',
			'editorial' => 'editorial-archive.php',
			default     => 'standard-page.php',
		};
		$data = array(
			'page'       => $this->current,
			'content'    => $page_content,
			'catalog'    => $this->catalog->rows(),
			'faq'        => $this->content->faq(),
			'components' => $this,
		);

		if ( 'editorial' === $this->current['type'] ) {
			$data['query'] = $this->editorial->query( (string) $this->current['key'] );
		}

		get_header();
		$this->template->renderFile( $this->path( $template ), $data );
		get_footer();
	}

	/**
	 * Render a reusable module component.
	 *
	 * @param string              $name Component filename without extension.
	 * @param array<string,mixed> $data Component data.
	 * @return void
	 */
	public function renderComponent( string $name, array $data = array() ): void {
		$name = sanitize_key( $name );
		$this->template->renderFile( $this->path( 'components/' . $name . '.php' ), $data );
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
