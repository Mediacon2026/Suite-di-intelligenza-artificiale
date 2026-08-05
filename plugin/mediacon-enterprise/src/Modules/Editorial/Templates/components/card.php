<?php
/**
 * Uniform editorial card.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="me-editorial-card">
	<a class="me-editorial-card__media me-editorial-card__media--<?php echo esc_attr( $card['image_ratio'] ); ?>" href="<?php echo esc_url( $card['url'] ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( $card['image'] ) :
			?>
			<img src="<?php echo esc_url( $card['image'] ); ?>" alt="" loading="lazy">
			<?php
else :
	?>
			<span class="me-editorial-card__fallback"><span><?php esc_html_e( 'Mediacon', 'mediacon-enterprise' ); ?></span></span><?php endif; ?>
	</a>
	<div class="me-editorial-card__body">
		<div class="me-editorial-card__meta">
			<?php
			if ( $card['badge'] ) :
				?>
				<span class="me-editorial__badge"><?php echo esc_html( $card['badge'] ); ?></span><?php endif; ?>
			<?php
			if ( $card['category'] ) :
				?>
				<span><?php echo esc_html( $card['category'] ); ?></span><?php endif; ?>
			<time><?php echo esc_html( $card['date'] ); ?></time>
		</div>
		<h2 class="me-editorial-card__title"><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h2>
		<?php
		if ( $card['number'] ) :
			?>
			<p class="me-editorial-card__number"><?php echo esc_html( $card['number'] ); ?></p><?php endif; ?>
		<p class="me-editorial-card__excerpt"><?php echo esc_html( $card['excerpt'] ); ?></p>
		<a class="me-editorial__text-link" href="<?php echo esc_url( $card['url'] ); ?>"><?php esc_html_e( 'Continua', 'mediacon-enterprise' ); ?><span class="screen-reader-text">: <?php echo esc_html( $card['title'] ); ?></span></a>
	</div>
</article>
