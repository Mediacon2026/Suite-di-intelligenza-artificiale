<?php
/**
 * WordPress course integration repository.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Services;

use Mediacon\Enterprise\Core\SettingsManager;
use WP_Post;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Reads existing WordPress posts and metadata as public course records.
 */
final class CourseRepository {

	/**
	 * Create the repository.
	 *
	 * @param SettingsManager $settings Core settings manager.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/**
	 * Query existing course posts with public filters.
	 *
	 * @param string $preset Optional upcoming or concluded preset.
	 * @return WP_Query
	 */
	public function query( string $preset = '' ): WP_Query {
		$general = $this->general();
		$search  = filter_input( INPUT_GET, 'formation_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$type    = $this->filterValue( 'formation_type', array( 'base', 'advanced', 'renewal', 'event', 'seminar', 'workshop' ) );
		$mode    = $this->filterValue( 'formation_mode', array( 'online', 'presenza', 'ibrida' ) );
		$status  = $this->filterValue( 'formation_status', array( 'iscrizioni-aperte', 'posti-esauriti', 'concluso' ) );
		$from    = $this->dateFilter( 'formation_from' );
		$to      = $this->dateFilter( 'formation_to' );
		$meta    = array();

		if ( '' !== $type ) {
			$meta[] = array(
				'key'   => '_mediacon_course_type',
				'value' => $type,
			);
		}
		if ( '' !== $mode ) {
			$meta[] = array(
				'key'   => '_mediacon_course_mode',
				'value' => $mode,
			);
		}
		if ( 'upcoming' === $preset ) {
			$meta[] = array(
				'key'     => '_mediacon_course_status',
				'value'   => array( 'iscrizioni-aperte', 'posti-esauriti' ),
				'compare' => 'IN',
			);
			$meta[] = array(
				'key'     => '_mediacon_course_start',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			);
		} elseif ( 'concluded' === $preset ) {
			$meta[] = array(
				'key'   => '_mediacon_course_status',
				'value' => 'concluso',
			);
		} elseif ( '' !== $status ) {
			$meta[] = array(
				'key'   => '_mediacon_course_status',
				'value' => $status,
			);
		}
		if ( '' !== $from ) {
			$meta[] = array(
				'key'     => '_mediacon_course_start',
				'value'   => $from,
				'compare' => '>=',
				'type'    => 'DATE',
			);
		}
		if ( '' !== $to ) {
			$meta[] = array(
				'key'     => '_mediacon_course_start',
				'value'   => $to,
				'compare' => '<=',
				'type'    => 'DATE',
			);
		}

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'category_name'       => $general['course_category'],
			'posts_per_page'      => $general['posts_per_page'],
			'paged'               => max( 1, (int) get_query_var( 'paged', 1 ) ),
			's'                   => is_string( $search ) ? sanitize_text_field( $search ) : '',
			'meta_key'            => '_mediacon_course_start', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required integration with existing course post metadata.
			'orderby'             => 'meta_value',
			'order'               => 'ASC',
			'ignore_sticky_posts' => true,
		);

		if ( array() !== $meta ) {
			$args['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Public filters use existing course metadata.
		}

		return new WP_Query( $args );
	}

	/**
	 * Query existing formation insight posts.
	 *
	 * @return WP_Query
	 */
	public function insights(): WP_Query {
		$search = filter_input( INPUT_GET, 'formation_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		return new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'category_name'       => $this->general()['insight_category'],
				'posts_per_page'      => $this->general()['posts_per_page'],
				'paged'               => max( 1, (int) get_query_var( 'paged', 1 ) ),
				's'                   => is_string( $search ) ? sanitize_text_field( $search ) : '',
				'ignore_sticky_posts' => true,
			)
		);
	}

	/**
	 * Determine whether the current post belongs to the configured course category.
	 *
	 * @return bool
	 */
	public function isCurrentCourse(): bool {
		return is_singular( 'post' ) && has_category( $this->general()['course_category'] );
	}

	/**
	 * Return normalized display data for a course post or page.
	 *
	 * @param int                 $post_id  WordPress post ID.
	 * @param array<string,mixed> $defaults Default content.
	 * @return array<string,mixed>
	 */
	public function data( int $post_id, array $defaults = array() ): array {
		$post          = get_post( $post_id );
		$program       = (string) get_post_meta( $post_id, '_mediacon_course_program', true );
		$start         = (string) get_post_meta( $post_id, '_mediacon_course_start', true );
		$enroll_url    = (string) get_post_meta( $post_id, '_mediacon_course_enrollment_url', true );
		$duration      = (string) get_post_meta( $post_id, '_mediacon_course_duration', true );
		$program_items = preg_split( '/\R/', $program );
		$program_items = false === $program_items ? array() : $program_items;

		return array(
			'id'           => $post_id,
			'title'        => $post instanceof WP_Post ? get_the_title( $post ) : ( $defaults['title'] ?? '' ),
			'url'          => (string) get_permalink( $post_id ),
			'excerpt'      => $post instanceof WP_Post ? wp_strip_all_tags( get_the_excerpt( $post ) ) : ( $defaults['intro'] ?? '' ),
			'image'        => (string) get_the_post_thumbnail_url( $post_id, 'medium_large' ),
			'type'         => sanitize_key( (string) get_post_meta( $post_id, '_mediacon_course_type', true ) ),
			'status'       => sanitize_key( (string) get_post_meta( $post_id, '_mediacon_course_status', true ) ),
			'start'        => $start,
			'date_label'   => $this->formatDate( $start ),
			'duration'     => '' !== $duration ? $duration : (string) ( $defaults['duration'] ?? '' ),
			'mode'         => (string) get_post_meta( $post_id, '_mediacon_course_mode', true ),
			'venue'        => (string) get_post_meta( $post_id, '_mediacon_course_venue', true ),
			'teachers'     => (string) get_post_meta( $post_id, '_mediacon_course_teachers', true ),
			'availability' => (string) get_post_meta( $post_id, '_mediacon_course_availability', true ),
			'price'        => (string) get_post_meta( $post_id, '_mediacon_course_price', true ),
			'enroll_url'   => '' !== $enroll_url ? $enroll_url : $this->general()['enrollment_url'],
			'program'      => array_values( array_filter( array_map( 'trim', $program_items ) ) ),
			'audience'     => (string) get_post_meta( $post_id, '_mediacon_course_audience', true ),
			'requirements' => (string) get_post_meta( $post_id, '_mediacon_course_requirements', true ),
		);
	}

	/**
	 * Return formation general settings with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public function general(): array {
		$formation = $this->settings->get( 'formation', array() );
		$general   = isset( $formation['general'] ) && is_array( $formation['general'] ) ? $formation['general'] : array();

		return wp_parse_args(
			$general,
			array(
				'course_category'         => 'corsi',
				'teacher_category'        => 'docenti',
				'insight_category'        => 'formazione',
				'enrollment_url'          => '',
				'posts_per_page'          => 9,
				'detail_template'         => false,
				'teacher_detail_template' => false,
			)
		);
	}

	/**
	 * Return a translated status label.
	 *
	 * @param string $status Status key.
	 * @return string
	 */
	public function statusLabel( string $status ): string {
		$labels = array(
			'iscrizioni-aperte' => __( 'Iscrizioni aperte', 'mediacon-enterprise' ),
			'posti-esauriti'    => __( 'Posti esauriti', 'mediacon-enterprise' ),
			'concluso'          => __( 'Concluso', 'mediacon-enterprise' ),
		);

		return $labels[ $status ] ?? __( 'In programmazione', 'mediacon-enterprise' );
	}

	/**
	 * Read and validate a public filter.
	 *
	 * @param string            $name    Query parameter name.
	 * @param array<int,string> $allowed Allowed values.
	 * @return string
	 */
	private function filterValue( string $name, array $allowed ): string {
		$value = filter_input( INPUT_GET, $name, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$value = is_string( $value ) ? sanitize_key( $value ) : '';

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * Read a valid ISO date query parameter.
	 *
	 * @param string $name Query parameter name.
	 * @return string
	 */
	private function dateFilter( string $name ): string {
		$value = filter_input( INPUT_GET, $name, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$value = is_string( $value ) ? sanitize_text_field( $value ) : '';

		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
	}

	/**
	 * Format an ISO-like course date through WordPress localization.
	 *
	 * @param string $date Stored date.
	 * @return string
	 */
	private function formatDate( string $date ): string {
		$timestamp = strtotime( $date );

		return false === $timestamp ? '' : wp_date( get_option( 'date_format' ), $timestamp );
	}
}
