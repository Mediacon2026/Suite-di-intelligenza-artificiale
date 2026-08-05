<?php
/**
 * Course status badge.
 *
 * @package MediaconEnterprise
 * @var string $label Badge label.
 * @var string $status Status key.
 */

defined( 'ABSPATH' ) || exit;
?>
<span class="me-formation__badge me-formation__badge--<?php echo esc_attr( sanitize_html_class( $status ) ); ?>"><?php echo esc_html( $label ); ?></span>
