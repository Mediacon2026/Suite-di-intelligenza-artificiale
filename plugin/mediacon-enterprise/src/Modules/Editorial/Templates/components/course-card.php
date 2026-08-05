<?php
/**
 * Course data rendered through the editorial card framework.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="me-editorial-card">
	<a class="me-editorial-card__media me-editorial-card__media--16-9" href="<?php echo esc_url( $course['url'] ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( $course['image'] ) :
			?>
			<img src="<?php echo esc_url( $course['image'] ); ?>" alt="" loading="lazy">
			<?php
else :
	?>
			<span class="me-editorial-card__fallback"><span><?php esc_html_e( 'Formazione Mediacon', 'mediacon-enterprise' ); ?></span></span><?php endif; ?>
	</a>
	<div class="me-editorial-card__body">
		<div class="me-editorial-card__meta"><span class="me-editorial__badge"><?php echo esc_html( $type_label ); ?></span><time><?php echo esc_html( $course['date_label'] ); ?></time></div>
		<h2 class="me-editorial-card__title"><a href="<?php echo esc_url( $course['url'] ); ?>"><?php echo esc_html( $course['title'] ); ?></a></h2>
		<div class="me-editorial-card__facts">
		<?php
		if ( $course['mode'] ) :
			?>
			<span><?php echo esc_html( ucfirst( $course['mode'] ) ); ?></span><?php endif; ?><span><?php echo esc_html( $status_label ); ?></span></div>
		<p class="me-editorial-card__excerpt"><?php echo esc_html( $course['excerpt'] ); ?></p>
		<a class="me-editorial__text-link" href="<?php echo esc_url( $course['url'] ); ?>"><?php esc_html_e( 'Scopri il corso', 'mediacon-enterprise' ); ?><span class="screen-reader-text">: <?php echo esc_html( $course['title'] ); ?></span></a>
	</div>
</article>
