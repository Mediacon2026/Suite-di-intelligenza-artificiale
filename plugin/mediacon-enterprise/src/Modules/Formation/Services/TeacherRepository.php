<?php
/**
 * WordPress teacher integration repository.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Services;

use Mediacon\Enterprise\Core\SettingsManager;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Reads existing posts as public teacher profiles without modifying users.
 */
final class TeacherRepository {

	/**
	 * Create the repository.
	 *
	 * @param SettingsManager $settings Core settings manager.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/**
	 * Query published teacher profile posts.
	 *
	 * @return WP_Query
	 */
	public function query(): WP_Query {
		$formation = $this->settings->get( 'formation', array() );
		$general   = isset( $formation['general'] ) && is_array( $formation['general'] ) ? $formation['general'] : array();

		return new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'category_name'       => sanitize_title( $general['teacher_category'] ?? 'docenti' ),
				'posts_per_page'      => -1,
				'orderby'             => 'title',
				'order'               => 'ASC',
				'ignore_sticky_posts' => true,
			)
		);
	}

	/**
	 * Determine whether the current post is a configured teacher profile.
	 *
	 * @return bool
	 */
	public function isCurrentTeacher(): bool {
		$formation = $this->settings->get( 'formation', array() );
		$general   = isset( $formation['general'] ) && is_array( $formation['general'] ) ? $formation['general'] : array();

		return is_singular( 'post' ) && has_category( sanitize_title( $general['teacher_category'] ?? 'docenti' ) );
	}

	/**
	 * Return normalized profile metadata.
	 *
	 * @param int $post_id Teacher post ID.
	 * @return array<string,string>
	 */
	public function data( int $post_id ): array {
		return array(
			'title'         => get_the_title( $post_id ),
			'image'         => (string) get_the_post_thumbnail_url( $post_id, 'large' ),
			'qualification' => (string) get_post_meta( $post_id, '_mediacon_teacher_qualification', true ),
			'biography'     => (string) get_post_field( 'post_content', $post_id ),
			'expertise'     => (string) get_post_meta( $post_id, '_mediacon_teacher_expertise', true ),
			'courses'       => (string) get_post_meta( $post_id, '_mediacon_teacher_courses', true ),
		);
	}
}
