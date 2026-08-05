<?php
/**
 * Formation breadcrumb.
 *
 * @package MediaconEnterprise
 * @var string $current Current page title.
 */

defined( 'ABSPATH' ) || exit;
?>
<nav class="me-formation__breadcrumb" aria-label="<?php echo esc_attr__( 'Percorso di navigazione', 'mediacon-enterprise' ); ?>">
	<ol class="me-formation__container">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Home', 'mediacon-enterprise' ); ?></a></li>
		<li aria-current="page"><?php echo esc_html( $current ); ?></li>
	</ol>
</nav>
