<?php
/**
 * Editorial integration repository.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation\Services;

use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Queries existing WordPress posts for mediation editorial archives.
 */
final class EditorialRepository {

	/**
	 * Query published posts from the archive category.
	 *
	 * @param string $archive Archive key.
	 * @return WP_Query
	 */
	public function query( string $archive ): WP_Query {
		$search = filter_input( INPUT_GET, 'mediation_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$year   = filter_input( INPUT_GET, 'mediation_year', FILTER_VALIDATE_INT );
		$page   = max( 1, (int) get_query_var( 'paged', 1 ) );
		$args   = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'category_name'       => 'legislation' === $archive ? 'normativa' : 'giurisprudenza',
			'posts_per_page'      => 9,
			'paged'               => $page,
			's'                   => is_string( $search ) ? sanitize_text_field( $search ) : '',
			'ignore_sticky_posts' => true,
		);

		if ( is_int( $year ) && 2000 <= $year && (int) gmdate( 'Y' ) >= $year ) {
			$args['year'] = $year;
		}

		return new WP_Query( $args );
	}
}
