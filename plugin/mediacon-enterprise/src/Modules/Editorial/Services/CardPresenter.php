<?php
/**
 * Editorial card presenter.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial\Services;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/**
 * Normalizes existing WordPress post data for uniform editorial cards.
 */
final class CardPresenter {

	/**
	 * Create the card presenter.
	 *
	 * @param SettingsManager $settings Core settings manager.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/**
	 * Return escaped-at-template display data for a published post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $archive Archive key.
	 * @return array<string,mixed>
	 */
	public function present( int $post_id, string $archive = 'blog' ): array {
		$categories = get_the_category( $post_id );
		$category   = ! empty( $categories ) ? $categories[0]->name : '';
		$excerpt    = (string) get_the_excerpt( $post_id );

		if ( '' === trim( $excerpt ) ) {
			$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		}

		return array(
			'id'          => $post_id,
			'title'       => $this->truncate( get_the_title( $post_id ), $this->general()['title_length'] ),
			'excerpt'     => $this->truncate( $excerpt, $this->general()['excerpt_length'] ),
			'url'         => (string) get_permalink( $post_id ),
			'image'       => (string) get_the_post_thumbnail_url( $post_id, 'medium_large' ),
			'category'    => $category,
			'date'        => get_the_date( '', $post_id ),
			'badge'       => $this->badge( $archive ),
			'number'      => 'jurisprudence' === $archive ? (string) get_post_meta( $post_id, '_mediacon_decision_number', true ) : '',
			'image_ratio' => $this->general()['image_ratio'],
		);
	}

	/**
	 * Truncate plain text at a configurable character boundary.
	 *
	 * @param string $text   Text to truncate.
	 * @param int    $length Maximum characters.
	 * @return string
	 */
	public function truncate( string $text, int $length ): string {
		$text = trim( wp_strip_all_tags( $text ) );

		return wp_html_excerpt( $text, max( 1, $length ), '…' );
	}

	/**
	 * Return card settings with safe defaults.
	 *
	 * @return array<string,mixed>
	 */
	public function general(): array {
		$editorial = $this->settings->get( 'editorial', array() );
		$general   = isset( $editorial['general'] ) && is_array( $editorial['general'] ) ? $editorial['general'] : array();

		return wp_parse_args(
			$general,
			array(
				'title_length'    => 70,
				'excerpt_length'  => 160,
				'image_ratio'     => '16-9',
				'columns'         => 3,
				'posts_per_page'  => 9,
				'single_template' => false,
			)
		);
	}

	/**
	 * Return the archive-specific badge.
	 *
	 * @param string $archive Archive key.
	 * @return string
	 */
	private function badge( string $archive ): string {
		return match ( $archive ) {
			'jurisprudence' => __( 'Sentenza', 'mediacon-enterprise' ),
			'legislation'    => __( 'Normativa', 'mediacon-enterprise' ),
			default          => '',
		};
	}
}
