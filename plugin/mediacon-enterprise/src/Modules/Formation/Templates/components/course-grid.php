<?php
/**
 * Course grid and optional pagination.
 *
 * @package MediaconEnterprise
 * @var \WP_Query $query Course query.
 * @var \Mediacon\Enterprise\Modules\Formation\Services\CourseRepository $repository Course repository.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 * @var bool $paginate Whether to display pagination.
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( $query->have_posts() ) : ?>
	<div class="me-formation__course-grid">
		<?php while ( $query->have_posts() ) : ?>
			<?php
			$query->the_post();
			$course = $repository->data( get_the_ID() );
			$components->renderComponent(
				'course-card',
				array(
					'course'       => $course,
					'status_label' => $repository->statusLabel( $course['status'] ),
				)
			);
			?>
		<?php endwhile; ?>
	</div>
	<?php if ( $paginate && $query->max_num_pages > 1 ) : ?>
		<nav class="me-formation__pagination" aria-label="<?php echo esc_attr__( 'Paginazione dei corsi', 'mediacon-enterprise' ); ?>">
			<?php
			echo wp_kses_post(
				paginate_links(
					array(
						'total'   => $query->max_num_pages,
						'current' => max( 1, (int) get_query_var( 'paged', 1 ) ),
						'type'    => 'list',
					)
				)
			);
			?>
		</nav>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<div class="me-formation__empty" role="status"><p><?php echo esc_html__( 'Nessun corso corrisponde ai criteri selezionati.', 'mediacon-enterprise' ); ?></p></div>
<?php endif; ?>
