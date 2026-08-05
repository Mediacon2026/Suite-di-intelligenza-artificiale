<?php
/**
 * Formation call to action.
 *
 * @package MediaconEnterprise
 * @var string $title       CTA title.
 * @var string $description CTA description.
 * @var string $label       Link label.
 * @var string $url         Link URL.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="me-formation__cta" aria-labelledby="me-formation-cta-title">
	<div class="me-formation__container">
		<div><h2 id="me-formation-cta-title"><?php echo esc_html( $title ); ?></h2><p><?php echo esc_html( $description ); ?></p></div>
		<a class="me-formation__button me-formation__button--light" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
	</div>
</section>
