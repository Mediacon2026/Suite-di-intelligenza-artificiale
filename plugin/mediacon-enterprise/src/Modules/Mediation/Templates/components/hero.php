<?php
/**
 * Institutional hero component.
 *
 * @package MediaconEnterprise
 * @var string $eyebrow Eyebrow text.
 * @var string $title   Main heading.
 * @var string $intro   Introductory text.
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="me-mediation__hero">
	<div class="me-mediation__container">
		<p class="me-mediation__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<p class="me-mediation__lead"><?php echo esc_html( $intro ); ?></p>
	</div>
</header>
