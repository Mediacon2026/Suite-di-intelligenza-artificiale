<?php
/**
 * Public formation landing page.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var array<string,array<string,mixed>> $catalog Page catalog.
 * @var \WP_Query $query Upcoming courses.
 * @var array<int,array<string,string>> $faq FAQ items.
 * @var \Mediacon\Enterprise\Modules\Formation\Services\CourseRepository $repository Course repository.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$archive_url  = ! empty( $catalog['upcoming']['url'] ) ? $catalog['upcoming']['url'] : home_url( '/prossimi-corsi/' );
$teachers_url = ! empty( $catalog['teachers']['url'] ) ? $catalog['teachers']['url'] : home_url( '/docenti-e-formatori/' );
$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section" aria-labelledby="me-paths-title">
		<div class="me-formation__container">
			<p class="me-formation__eyebrow"><?php echo esc_html__( 'Percorsi', 'mediacon-enterprise' ); ?></p>
			<h2 id="me-paths-title"><?php echo esc_html__( 'Scegli il percorso formativo', 'mediacon-enterprise' ); ?></h2>
			<div class="me-formation__path-grid">
				<?php foreach ( array( 'base-course', 'advanced', 'renewal' ) as $path_key ) : ?>
					<?php if ( ! empty( $catalog[ $path_key ]['url'] ) ) : ?>
						<a class="me-formation__path-card" href="<?php echo esc_url( $catalog[ $path_key ]['url'] ); ?>"><span><?php echo esc_html( $catalog[ $path_key ]['title'] ); ?></span><strong aria-hidden="true">→</strong></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<section class="me-formation__section me-formation__section--muted" aria-labelledby="me-upcoming-title">
		<div class="me-formation__container">
			<div class="me-formation__section-heading"><h2 id="me-upcoming-title"><?php echo esc_html__( 'Prossimi corsi', 'mediacon-enterprise' ); ?></h2><a class="me-formation__text-link" href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html__( 'Vedi tutti', 'mediacon-enterprise' ); ?></a></div>
			<?php
			$components->renderComponent(
				'course-grid',
				array(
					'query'      => $query,
					'repository' => $repository,
					'components' => $components,
					'paginate'   => false,
				)
			);
			?>
		</div>
	</section>
	<section class="me-formation__section me-formation__split" aria-labelledby="me-teachers-title">
		<div class="me-formation__container me-formation__split-inner">
			<div><p class="me-formation__eyebrow"><?php echo esc_html__( 'Esperienza', 'mediacon-enterprise' ); ?></p><h2 id="me-teachers-title"><?php echo esc_html__( 'Docenti e formatori', 'mediacon-enterprise' ); ?></h2></div>
			<div><p class="me-formation__lead"><?php echo esc_html__( 'Scopri i profili professionali coinvolti nelle attività formative pubblicate da Mediacon.', 'mediacon-enterprise' ); ?></p><a class="me-formation__button" href="<?php echo esc_url( $teachers_url ); ?>"><?php echo esc_html__( 'Conosci i docenti', 'mediacon-enterprise' ); ?></a></div>
		</div>
	</section>
	<?php $components->renderComponent( 'faq', array( 'items' => $faq ) ); ?>
</main>
