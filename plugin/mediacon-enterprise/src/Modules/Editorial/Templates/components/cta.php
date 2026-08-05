<?php
/**
 * Editorial closing call to action.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="me-editorial__cta">
	<div class="me-editorial__container"><div><p class="me-editorial__eyebrow"><?php esc_html_e( 'Mediacon', 'mediacon-enterprise' ); ?></p><h2><?php echo esc_html( $title ); ?></h2><p><?php echo esc_html( $description ); ?></p></div><a class="me-editorial__button me-editorial__button--light" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></div>
</section>
