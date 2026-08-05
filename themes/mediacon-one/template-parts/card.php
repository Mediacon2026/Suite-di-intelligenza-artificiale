<?php
/**
 * Uniform editorial card.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
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
		); else :
			?>
		<span class="editorial-card__placeholder"></span><?php endif; ?></a>
	<div class="editorial-card__body"><div class="editorial-card__meta"><?php mediacon_one_posted_on(); ?></div><h2 class="editorial-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p class="editorial-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p><a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leggi', 'mediacon-one' ); ?><span aria-hidden="true"> →</span></a></div>
</article>
