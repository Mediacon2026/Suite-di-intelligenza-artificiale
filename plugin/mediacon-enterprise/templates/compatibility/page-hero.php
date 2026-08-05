<?php
/**
 * Design Core-compatible page hero component.
 *
 * @package MediaconEnterprise
 * @var string $eyebrow    Eyebrow text.
 * @var string $title      Page title.
 * @var string $description Introductory text.
 */

defined( 'ABSPATH' ) || exit;

$allowed_title_html = array(
	'em'     => array(),
	'strong' => array(),
);
?>
<header class="mdc-page-hero">
	<div class="mdc-container">
		<p class="mdc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h1 class="mdc-heading mdc-heading--light"><?php echo wp_kses( $title, $allowed_title_html ); ?></h1>
		<p class="mdc-intro"><?php echo esc_html( $description ); ?></p>
	</div>
</header>
