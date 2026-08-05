<?php
/**
 * Formation institutional hero.
 *
 * @package MediaconEnterprise
 * @var string $eyebrow Eyebrow text.
 * @var string $title   Page title.
 * @var string $intro   Introductory text.
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="me-formation__hero">
	<div class="me-formation__container">
		<p class="me-formation__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<p class="me-formation__lead"><?php echo esc_html( $intro ); ?></p>
	</div>
</header>
