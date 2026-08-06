<?php
/**
 * Uniform editorial and course card.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$course_category = mediacon_one_page_map()['upcoming_courses']['path'] ?? 'prossimi-corsi';
$is_course       = has_category( (string) $course_category );
$course_status   = $is_course ? mediacon_one_course_status( get_the_ID() ) : array();
?>
<article <?php post_class( 'editorial-card' ); ?>>
	<a class="editorial-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
	<?php
	if ( has_post_thumbnail() ) :
		the_post_thumbnail(
			'mediacon-one-card',
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
	else :
		?>
		<span class="editorial-card__placeholder"></span>
	<?php endif; ?>
	</a>
	<div class="editorial-card__body">
		<div class="editorial-card__meta">
			<?php mediacon_one_posted_on(); ?>
			<?php if ( $is_course ) : ?>
				<span class="badge badge--<?php echo esc_attr( $course_status['key'] ); ?>"><?php echo esc_html( $course_status['label'] ); ?></span>
			<?php endif; ?>
		</div>
		<h2 class="editorial-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="editorial-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<a class="button editorial-card__button" href="<?php the_permalink(); ?>"><?php echo esc_html( $is_course ? __( 'Dettagli del corso', 'mediacon-one' ) : __( 'Leggi', 'mediacon-one' ) ); ?></a>
	</div>
</article>
