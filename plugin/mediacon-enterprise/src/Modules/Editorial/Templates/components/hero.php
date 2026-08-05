<?php
/**
 * Editorial archive hero.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="me-editorial-hero">
	<div class="me-editorial-hero__inner">
		<p class="me-editorial__eyebrow"><?php echo esc_html__( 'Mediacon Editoriale', 'mediacon-enterprise' ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php
		if ( ! empty( $description ) ) :
			?>
			<p class="me-editorial-hero__intro"><?php echo esc_html( $description ); ?></p><?php endif; ?>
	</div>
</header>
