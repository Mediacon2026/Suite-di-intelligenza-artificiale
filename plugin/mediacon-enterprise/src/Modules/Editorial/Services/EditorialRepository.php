<?php
/**
 * Editorial content repository.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial\Services;

use Mediacon\Enterprise\Modules\Editorial\Support\ArchiveCatalog;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Builds standard WordPress queries for existing editorial posts.
 */
final class EditorialRepository {

	/**
	 * Create the editorial repository.
	 *
	 * @param ArchiveCatalog $catalog Archive catalog.
	 * @param CardPresenter  $cards   Card presenter.
	 */
	public function __construct(
		private readonly ArchiveCatalog $catalog,
		private readonly CardPresenter $cards
	) {}

	/**
	 * Query published posts for an editorial archive.
	 *
	 * @param string $archive Archive key.
	 * @return WP_Query
	 */
	public function query( string $archive ): WP_Query {
		return new WP_Query( $this->buildQueryArgs( $archive, $this->requestFilters() ) );
	}

	/**
	 * Build testable WordPress query arguments.
	 *
	 * @param string              $archive Archive key.
	 * @param array<string,mixed> $filters Validated filters.
	 * @return array<string,mixed>
	 */
	public function buildQueryArgs( string $archive, array $filters ): array {
		$rows        = $this->catalog->rows();
		$category_id = absint( $rows[ $archive ]['category_id'] ?? 0 );
		$order       = in_array( $filters['order'] ?? '', array( 'oldest', 'title' ), true ) ? $filters['order'] : 'newest';
		$args        = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => (int) $this->cards->general()['posts_per_page'],
			'paged'               => max( 1, absint( $filters['page'] ?? 1 ) ),
			's'                   => sanitize_text_field( (string) ( $filters['search'] ?? '' ) ),
			'ignore_sticky_posts' => true,
		);

		if ( $category_id > 0 ) {
			$args['cat'] = $category_id;
		}
		if ( 'categories' === $archive && is_category() ) {
			$args['cat'] = get_queried_object_id();
		}
		if ( absint( $filters['category'] ?? 0 ) > 0 && 'blog' === $archive ) {
			$args['cat'] = absint( $filters['category'] );
		}
		$content_type = sanitize_key( (string) ( $filters['type'] ?? '' ) );
		if ( isset( $rows[ $content_type ] ) && absint( $rows[ $content_type ]['category_id'] ?? 0 ) > 0 ) {
			$args['cat'] = absint( $rows[ $content_type ]['category_id'] );
		}
		if ( absint( $filters['year'] ?? 0 ) >= 2000 ) {
			$args['year'] = absint( $filters['year'] );
		}
		if ( absint( $filters['topic'] ?? 0 ) > 0 ) {
			$args['tag_id'] = absint( $filters['topic'] );
		}
		if ( 'title' === $order ) {
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
		} else {
			$args['orderby'] = 'date';
			$args['order']   = 'oldest' === $order ? 'ASC' : 'DESC';
		}

		$authority = sanitize_text_field( (string) ( $filters['authority'] ?? '' ) );
		if ( '' !== $authority && in_array( $archive, array( 'jurisprudence', 'legislation' ), true ) ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Optional public filtering of existing editorial metadata.
				array(
					'key'   => 'jurisprudence' === $archive ? '_mediacon_judicial_body' : '_mediacon_legal_source',
					'value' => $authority,
				),
			);
		}

		return $args;
	}

	/**
	 * Return filter options derived from existing posts only.
	 *
	 * @param string $archive Archive key.
	 * @return array{years:array<int,int>,authorities:array<int,string>,topics:array<int,\WP_Term>}
	 */
	public function facets( string $archive ): array {
		$args                   = $this->buildQueryArgs( $archive, array( 'page' => 1 ) );
		$args['fields']         = 'ids';
		$args['posts_per_page'] = 50;
		unset( $args['paged'], $args['s'], $args['meta_query'] );
		$ids         = get_posts( $args );
		$years       = array();
		$authorities = array();
		$meta_key    = 'jurisprudence' === $archive ? '_mediacon_judicial_body' : '_mediacon_legal_source';

		foreach ( $ids as $post_id ) {
			$year = (int) get_the_date( 'Y', $post_id );
			if ( $year > 0 ) {
				$years[ $year ] = $year;
			}
			if ( in_array( $archive, array( 'jurisprudence', 'legislation' ), true ) ) {
				$value = trim( (string) get_post_meta( $post_id, $meta_key, true ) );
				if ( '' !== $value ) {
					$authorities[ $value ] = $value;
				}
			}
		}

		krsort( $years );
		natcasesort( $authorities );

		return array(
			'years'       => array_values( $years ),
			'authorities' => array_values( $authorities ),
			'topics'      => get_tags( array( 'hide_empty' => true ) ),
		);
	}

	/**
	 * Query related posts from existing categories.
	 *
	 * @param int $post_id Current post ID.
	 * @return WP_Query
	 */
	public function related( int $post_id ): WP_Query {
		$category_ids = wp_get_post_categories( $post_id );

		return new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 3,
				'post__not_in'        => array( $post_id ),
				'category__in'        => $category_ids,
				'ignore_sticky_posts' => true,
			)
		);
	}

	/**
	 * Read and sanitize public query-string filters.
	 *
	 * @return array<string,mixed>
	 */
	private function requestFilters(): array {
		$search = filter_input( INPUT_GET, 'editorial_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! is_string( $search ) ) {
			$search = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		}
		$category  = filter_input( INPUT_GET, 'editorial_category', FILTER_VALIDATE_INT );
		$year      = filter_input( INPUT_GET, 'editorial_year', FILTER_VALIDATE_INT );
		$topic     = filter_input( INPUT_GET, 'editorial_topic', FILTER_VALIDATE_INT );
		$authority = filter_input( INPUT_GET, 'editorial_authority', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$order     = filter_input( INPUT_GET, 'editorial_order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$type      = filter_input( INPUT_GET, 'editorial_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		return array(
			'search'    => is_string( $search ) ? $search : '',
			'category'  => is_int( $category ) ? $category : 0,
			'year'      => is_int( $year ) ? $year : 0,
			'topic'     => is_int( $topic ) ? $topic : 0,
			'authority' => is_string( $authority ) ? $authority : '',
			'order'     => sanitize_key( is_string( $order ) ? $order : 'newest' ),
			'type'      => sanitize_key( is_string( $type ) ? $type : '' ),
			'page'      => max( 1, (int) get_query_var( 'paged', 1 ) ),
		);
	}
}
