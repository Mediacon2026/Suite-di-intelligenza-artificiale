<?php
/**
 * Public teacher archive.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var \WP_Query $query Teacher query.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section">
		<div class="me-formation__container">
			<?php if ( $query->have_posts() ) : ?>
				<div class="me-formation__teacher-grid">
					<?php while ( $query->have_posts() ) : ?>
						<?php $query->the_post(); ?>
						<article class="me-formation__teacher-card">
							<div class="me-formation__teacher-image">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) ); }
							?>
							</div>
							<div class="me-formation__teacher-body">
								<h2><?php the_title(); ?></h2>
								<?php $qualification = (string) get_post_meta( get_the_ID(), '_mediacon_teacher_qualification', true ); ?>
								<?php $expertise = (string) get_post_meta( get_the_ID(), '_mediacon_teacher_expertise', true ); ?>
								<?php $courses = (string) get_post_meta( get_the_ID(), '_mediacon_teacher_courses', true ); ?>
								<?php
								if ( $qualification ) :
									?>
									<p class="me-formation__teacher-role"><?php echo esc_html( $qualification ); ?></p><?php endif; ?>
								<p class="me-formation__clamp"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
								<?php
								if ( $expertise ) :
									?>
									<p><strong><?php echo esc_html__( 'Competenze:', 'mediacon-enterprise' ); ?></strong> <?php echo esc_html( $expertise ); ?></p><?php endif; ?>
								<?php
								if ( $courses ) :
									?>
									<p><strong><?php echo esc_html__( 'Corsi associati:', 'mediacon-enterprise' ); ?></strong> <?php echo esc_html( $courses ); ?></p><?php endif; ?>
								<a class="me-formation__text-link" href="<?php the_permalink(); ?>"><?php echo esc_html__( 'Leggi il profilo', 'mediacon-enterprise' ); ?><span class="screen-reader-text">: <?php the_title(); ?></span></a>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="me-formation__empty" role="status"><p><?php echo esc_html__( 'Nessun profilo docente è attualmente pubblicato.', 'mediacon-enterprise' ); ?></p></div>
			<?php endif; ?>
		</div>
	</section>
</main>
