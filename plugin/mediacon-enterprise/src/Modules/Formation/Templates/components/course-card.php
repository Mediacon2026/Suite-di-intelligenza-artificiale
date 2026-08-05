<?php
/**
 * Uniform course card.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $course Course display data.
 * @var string              $status_label Translated status.
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="me-formation__course-card">
	<?php if ( $course['image'] ) : ?>
		<a class="me-formation__course-image" href="<?php echo esc_url( $course['url'] ); ?>" tabindex="-1" aria-hidden="true">
			<img src="<?php echo esc_url( $course['image'] ); ?>" alt="" loading="lazy">
		</a>
	<?php else : ?>
		<div class="me-formation__course-image me-formation__course-image--empty" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="me-formation__course-body">
		<?php if ( $course['status'] ) : ?>
			<span class="me-formation__badge me-formation__badge--<?php echo esc_attr( sanitize_html_class( $course['status'] ) ); ?>"><?php echo esc_html( $status_label ); ?></span>
		<?php endif; ?>
		<h3><a href="<?php echo esc_url( $course['url'] ); ?>"><?php echo esc_html( $course['title'] ); ?></a></h3>
		<div class="me-formation__course-meta">
			<?php
			if ( $course['date_label'] ) :
				?>
				<span><?php echo esc_html( $course['date_label'] ); ?></span><?php endif; ?>
			<?php
			if ( $course['duration'] ) :
				?>
				<span><?php echo esc_html( $course['duration'] ); ?></span><?php endif; ?>
			<?php
			if ( $course['mode'] ) :
				?>
				<span><?php echo esc_html( ucfirst( $course['mode'] ) ); ?></span><?php endif; ?>
		</div>
		<p class="me-formation__clamp"><?php echo esc_html( $course['excerpt'] ); ?></p>
		<a class="me-formation__text-link" href="<?php echo esc_url( $course['url'] ); ?>"><?php echo esc_html__( 'Scopri il corso', 'mediacon-enterprise' ); ?><span class="screen-reader-text">: <?php echo esc_html( $course['title'] ); ?></span></a>
	</div>
</article>
