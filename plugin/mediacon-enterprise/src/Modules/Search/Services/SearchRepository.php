<?php
/**
 * Bounded WordPress search repository.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Services;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Support\Content as FormationContent;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog as FormationPages;
use Mediacon\Enterprise\Modules\Mediation\Support\Content as MediationContent;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog as MediationPages;
use WP_Post;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Reads only published WordPress content and configured FAQ copy.
 */
final readonly class SearchRepository {

	/**
	 * Create the repository.
	 *
	 * @param SettingsManager  $settings   Core settings.
	 * @param MediationPages   $mediation  Mediation page catalog.
	 * @param FormationPages   $formation  Formation page catalog.
	 * @param MediationContent $mediation_content Mediation content.
	 * @param FormationContent $formation_content Formation content.
	 * @param CardPresenter    $cards Editorial card presenter.
	 * @param CourseRepository $courses Formation course repository.
	 */
	public function __construct(
		private SettingsManager $settings,
		private MediationPages $mediation,
		private FormationPages $formation,
		private MediationContent $mediation_content,
		private FormationContent $formation_content,
		private CardPresenter $cards,
		private CourseRepository $courses
	) {}

	/**
	 * Find a bounded set of published candidates.
	 *
	 * @param array<string> $terms Search terms.
	 * @param int           $limit Maximum candidates.
	 * @return array<array<string,mixed>>
	 */
	public function find( array $terms, int $limit ): array {
		$faqs       = $this->faqDocuments();
		$limit      = max( 1, $limit );
		$post_limit = max( 0, $limit - count( $faqs ) );
		$documents  = array();
		$seen       = array();
		$terms      = array_slice( array_values( array_unique( $terms ) ), 0, 8 );
		$per_query  = min( 50, max( 1, (int) ceil( $post_limit / max( 1, count( $terms ) ) ) ) );

		if ( 0 === $post_limit ) {
			return array_slice( $faqs, 0, $limit );
		}

		foreach ( $terms as $term ) {
			$query = new WP_Query( $this->buildQueryArgs( $term, $per_query ) );
			foreach ( $query->posts ?? array() as $post ) {
				if ( ! $post instanceof WP_Post || isset( $seen[ $post->ID ] ) ) {
					continue;
				}
				$document = $this->document( $post );
				if ( null !== $document ) {
					$documents[]       = $document;
					$seen[ $post->ID ] = true;
				}
				if ( count( $documents ) >= $post_limit ) {
					break 2;
				}
			}
		}

		return array_slice( array_merge( $documents, $faqs ), 0, $limit );
	}

	/**
	 * Build safe WordPress query arguments.
	 *
	 * @param string $term  Search term.
	 * @param int    $limit Result limit.
	 * @return array<string,mixed>
	 */
	public function buildQueryArgs( string $term, int $limit ): array {
		$config = $this->config();

		return array(
			'post_type'              => array( 'page', 'post' ),
			'post_status'            => 'publish',
			'posts_per_page'         => min( 50, max( 1, $limit ) ),
			's'                      => sanitize_text_field( $term ),
			'post__not_in'           => array_map( 'absint', $config['excluded_ids'] ?? array() ),
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => true,
		);
	}

	/**
	 * Normalize a public post into a search document.
	 *
	 * @param WP_Post $post Published post.
	 * @return array<string,mixed>|null
	 */
	private function document( WP_Post $post ): ?array {
		if ( 'publish' !== get_post_status( $post ) ) {
			return null;
		}
		$type = $this->classify( $post );
		if ( null === $type || ! $this->included( $type ) ) {
			return null;
		}
		$categories = get_the_category( $post->ID );
		$taxonomy   = implode( ' ', array_map( static fn ( $term ): string => (string) $term->name, $categories ) );
		$excerpt    = (string) get_the_excerpt( $post );
		if ( '' === trim( $excerpt ) ) {
			$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post->ID ) );
		}

		return array(
			'id'       => $post->ID,
			'status'   => 'publish',
			'type'     => $type,
			'label'    => $this->label( $type ),
			'title'    => get_the_title( $post ),
			'excerpt'  => $this->cards->truncate( $excerpt, 180 ),
			'content'  => wp_strip_all_tags( (string) get_post_field( 'post_content', $post->ID ) ),
			'taxonomy' => $taxonomy,
			'area'     => $this->area( $type ),
			'url'      => (string) get_permalink( $post ),
			'image'    => (string) get_the_post_thumbnail_url( $post->ID, 'medium_large' ),
			'date'     => (string) get_post_field( 'post_date', $post->ID ),
			'year'     => (int) get_the_date( 'Y', $post ),
		);
	}

	/**
	 * Classify content using existing module configuration.
	 *
	 * @param WP_Post $post WordPress post.
	 * @return string|null
	 */
	private function classify( WP_Post $post ): ?string {
		$enabled   = $this->enabledModules();
		$post_type = get_post_type( $post );
		if ( 'page' === $post_type ) {
			$mediation = $this->pageKeys( $this->mediation->rows() );
			$formation = $this->pageKeys( $this->formation->rows() );
			if ( isset( $mediation[ $post->ID ] ) ) {
				return in_array( 'mediation', $enabled, true ) ? ( 'faq' === $mediation[ $post->ID ] ? 'faq' : 'mediation' ) : null;
			}
			if ( isset( $formation[ $post->ID ] ) ) {
				return in_array( 'formation', $enabled, true ) ? ( 'faq' === $formation[ $post->ID ] ? 'faq' : 'formation' ) : null;
			}
			return 'pages';
		}
		if ( ! in_array( 'editorial', $enabled, true ) && ! in_array( 'formation', $enabled, true ) ) {
			return null;
		}
		$slugs   = array_map( static fn ( $term ): string => (string) $term->slug, get_the_category( $post->ID ) );
		$general = $this->courses->general();
		if ( in_array( sanitize_title( $general['course_category'] ?? 'corsi' ), $slugs, true ) ) {
			return in_array( 'formation', $enabled, true ) ? 'courses' : null;
		}
		if ( in_array( sanitize_title( $general['teacher_category'] ?? 'docenti' ), $slugs, true ) ) {
			return in_array( 'formation', $enabled, true ) ? 'teachers' : null;
		}
		if ( ! in_array( 'editorial', $enabled, true ) ) {
			return null;
		}
		if ( in_array( 'giurisprudenza', $slugs, true ) ) {
			return 'jurisprudence';
		}
		if ( in_array( 'normativa', $slugs, true ) ) {
			return 'legislation';
		}
		if ( in_array( 'approfondimenti', $slugs, true ) ) {
			return 'insights';
		}
		return 'blog';
	}

	/**
	 * Build virtual FAQ documents from existing module copy.
	 *
	 * @return array<array<string,mixed>>
	 */
	private function faqDocuments(): array {
		if ( ! $this->included( 'faq' ) ) {
			return array();
		}
		$documents = array();
		$enabled   = $this->enabledModules();
		$sources   = array();
		if ( in_array( 'mediation', $enabled, true ) ) {
			$sources[] = array( $this->mediation_content->faq(), $this->faqUrl( $this->mediation->rows() ), 'Mediazione' );
		}
		if ( in_array( 'formation', $enabled, true ) ) {
			$sources[] = array( $this->formation_content->faq(), $this->faqUrl( $this->formation->rows() ), 'Formazione' );
		}
		foreach ( $sources as $source_index => $source ) {
			foreach ( $source[0] as $index => $item ) {
				$documents[] = array(
					'id'       => 'faq-' . $source_index . '-' . $index,
					'status'   => 'publish',
					'type'     => 'faq',
					'label'    => 'FAQ',
					'title'    => (string) $item['question'],
					'excerpt'  => (string) $item['answer'],
					'content'  => (string) $item['answer'],
					'taxonomy' => (string) $source[2],
					'area'     => (string) $source[2],
					'url'      => (string) $source[1],
					'image'    => '',
					'date'     => '',
					'year'     => 0,
				);
			}
		}
		return $documents;
	}

	/**
	 * Return normalized search configuration.
	 *
	 * @return array<string,mixed>
	 */
	private function config(): array {
		$value = $this->settings->get( 'search', array() );
		return is_array( $value ) ? $value : array();
	}

	/**
	 * Return enabled module identifiers.
	 *
	 * @return array<string>
	 */
	private function enabledModules(): array {
		$value = $this->settings->get( 'enabled_modules', array() );
		return is_array( $value ) ? array_map( 'sanitize_key', $value ) : array();
	}

	/**
	 * Determine whether a content type is included.
	 *
	 * @param string $type Content type.
	 * @return bool
	 */
	private function included( string $type ): bool {
		$included = $this->config()['included'] ?? array();
		return ! is_array( $included ) || ! array_key_exists( $type, $included ) || ! empty( $included[ $type ] );
	}

	/**
	 * Index configured page rows by post ID.
	 *
	 * @param array<string,array<string,mixed>> $rows Page rows.
	 * @return array<int,string>
	 */
	private function pageKeys( array $rows ): array {
		$keys = array();
		foreach ( $rows as $key => $row ) {
			if ( 0 < (int) ( $row['page_id'] ?? 0 ) ) {
				$keys[ (int) $row['page_id'] ] = (string) $key;
			}
		}
		return $keys;
	}

	/**
	 * Resolve a configured FAQ URL.
	 *
	 * @param array<string,array<string,mixed>> $rows Page rows.
	 * @return string
	 */
	private function faqUrl( array $rows ): string {
		return (string) ( $rows['faq']['url'] ?? home_url( '/' ) );
	}

	/**
	 * Return a public type label.
	 *
	 * @param string $type Type key.
	 * @return string
	 */
	private function label( string $type ): string {
		$labels = array(
			'pages'         => 'Pagina',
			'mediation'     => 'Mediazione',
			'formation'     => 'Formazione',
			'blog'          => 'Blog',
			'jurisprudence' => 'Sentenza',
			'legislation'   => 'Normativa',
			'insights'      => 'Approfondimento',
			'courses'       => 'Corso',
			'teachers'      => 'Docente',
			'faq'           => 'FAQ',
		);
		return $labels[ $type ] ?? 'Contenuto';
	}

	/**
	 * Return the broad content area.
	 *
	 * @param string $type Type key.
	 * @return string
	 */
	private function area( string $type ): string {
		return in_array( $type, array( 'formation', 'courses', 'teachers' ), true ) ? 'Formazione' : ( 'mediation' === $type ? 'Mediazione' : 'Editoriale' );
	}
}
