<?php
/**
 * Call-to-action component.
 *
 * @package MediaconEnterprise
 * @var string $title       CTA title.
 * @var string $description CTA description.
 * @var string $label       Link label.
 * @var string $url         Link URL.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="me-mediation__cta" aria-labelledby="me-cta-title">
	<div class="me-mediation__container">
		<div>
			<h2 id="me-cta-title"><?php echo esc_html( $title ); ?></h2>
			<p><?php echo esc_html( $description ); ?></p>
		</div>
		<a class="me-mediation__button me-mediation__button--light" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
	</div>
</section>
