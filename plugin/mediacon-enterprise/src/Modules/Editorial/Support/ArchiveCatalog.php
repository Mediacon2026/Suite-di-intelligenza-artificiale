<?php
/**
 * Editorial archive catalog.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial\Support;

use Mediacon\Enterprise\Core\SettingsManager;
use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves existing WordPress editorial archives without changing content.
 */
final class ArchiveCatalog {

	/**
	 * Create the archive catalog.
	 *
	 * @param SettingsManager $settings Core settings manager.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/**
	 * Return supported archive definitions.
	 *
	 * @return array<string,array{title:string,slug:string,type:string}>
	 */
	public function definitions(): array {
		return array(
			'blog'          => array(
				'title' => __( 'Blog', 'mediacon-enterprise' ),
				'slug'  => '',
				'type'  => 'posts',
			),
			'jurisprudence' => array(
				'title' => __( 'Sentenze e Giurisprudenza', 'mediacon-enterprise' ),
				'slug'  => 'giurisprudenza',
				'type'  => 'category',
			),
			'legislation'   => array(
				'title' => __( 'Normativa', 'mediacon-enterprise' ),
				'slug'  => 'normativa',
				'type'  => 'category',
			),
			'insights'      => array(
				'title' => __( 'Approfondimenti', 'mediacon-enterprise' ),
				'slug'  => 'approfondimenti',
				'type'  => 'category',
			),
			'categories'    => array(
				'title' => __( 'Archivi categoria', 'mediacon-enterprise' ),
				'slug'  => '',
				'type'  => 'all-categories',
			),
			'courses'       => array(
				'title' => __( 'Prossimi corsi', 'mediacon-enterprise' ),
				'slug'  => 'prossimi-corsi',
				'type'  => 'courses',
			),
			'search'        => array(
				'title' => __( 'Ricerca editoriale', 'mediacon-enterprise' ),
				'slug'  => '',
				'type'  => 'search',
			),
			'single'        => array(
				'title' => __( 'Singoli articoli', 'mediacon-enterprise' ),
				'slug'  => '',
				'type'  => 'single',
			),
		);
	}

	/**
	 * Return definitions enriched with detected WordPress state.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function rows(): array {
		$settings = $this->settings();
		$rows     = array();

		foreach ( $this->definitions() as $key => $definition ) {
			$category_id = absint( $settings['categories'][ $key ] ?? 0 );
			$page_id     = 0;
			$url         = '';

			if ( 'category' === $definition['type'] ) {
				$term = $category_id > 0 ? get_term( $category_id, 'category' ) : get_category_by_slug( $definition['slug'] );
				if ( $term instanceof WP_Term ) {
					$category_id = (int) $term->term_id;
					$term_url    = get_term_link( $term );
					$url         = is_wp_error( $term_url ) ? '' : (string) $term_url;
				}
			} elseif ( 'posts' === $definition['type'] ) {
				$page_id = (int) get_option( 'page_for_posts', 0 );
				$url     = $page_id > 0 ? (string) get_permalink( $page_id ) : home_url( '/' );
			} elseif ( 'courses' === $definition['type'] ) {
				$page_id = $this->formationArchiveId( $definition['slug'] );
				$url     = $page_id > 0 ? (string) get_permalink( $page_id ) : '';
			} elseif ( 'search' === $definition['type'] ) {
				$url = home_url( '/?s=' );
			}

			$rows[ $key ] = array_merge(
				$definition,
				array(
					'key'         => $key,
					'category_id' => $category_id,
					'page_id'     => $page_id,
					'url'         => $url,
					'enabled'     => ! empty( $settings['templates'][ $key ] ),
				)
			);
		}

		return $rows;
	}

	/**
	 * Resolve an enabled archive for the current request.
	 *
	 * @return array<string,mixed>|null
	 */
	public function current(): ?array {
		foreach ( $this->rows() as $row ) {
			if ( ! $row['enabled'] ) {
				continue;
			}

			if ( 'posts' === $row['type'] && is_home() ) {
				return $row;
			}
			if ( 'category' === $row['type'] && $row['category_id'] > 0 && is_category( $row['category_id'] ) ) {
				return $row;
			}
			if ( 'all-categories' === $row['type'] && is_category() ) {
				$row['category_id'] = get_queried_object_id();
				return $row;
			}
			if ( 'courses' === $row['type'] && $row['page_id'] > 0 && is_page( $row['page_id'] ) ) {
				return $row;
			}
			if ( 'search' === $row['type'] && is_search() ) {
				return $row;
			}
		}

		return null;
	}

	/**
	 * Return normalized editorial settings.
	 *
	 * @return array<string,mixed>
	 */
	public function settings(): array {
		$value = $this->settings->get( 'editorial', array() );

		return array(
			'categories' => isset( $value['categories'] ) && is_array( $value['categories'] ) ? $value['categories'] : array(),
			'templates'  => isset( $value['templates'] ) && is_array( $value['templates'] ) ? $value['templates'] : array(),
			'general'    => isset( $value['general'] ) && is_array( $value['general'] ) ? $value['general'] : array(),
		);
	}

	/**
	 * Resolve the Formation archive without creating a page.
	 *
	 * @param string $slug Conventional archive slug.
	 * @return int
	 */
	private function formationArchiveId( string $slug ): int {
		$formation = $this->settings->get( 'formation', array() );
		$page_id   = absint( $formation['pages']['upcoming'] ?? 0 );

		if ( 0 === $page_id ) {
			$page    = get_page_by_path( $slug, OBJECT, 'page' );
			$page_id = $page instanceof \WP_Post ? (int) $page->ID : 0;
		}

		return $page_id;
	}
}
