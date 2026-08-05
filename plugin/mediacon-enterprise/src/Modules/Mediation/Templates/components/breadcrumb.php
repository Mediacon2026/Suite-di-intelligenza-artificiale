<?php
/**
 * Accessible breadcrumb component.
 *
 * @package MediaconEnterprise
 * @var string $current Current page name.
 */

defined( 'ABSPATH' ) || exit;
?>
<nav class="me-mediation__breadcrumb" aria-label="<?php echo esc_attr__( 'Percorso di navigazione', 'mediacon-enterprise' ); ?>">
	<ol class="me-mediation__container">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Home', 'mediacon-enterprise' ); ?></a></li>
		<li aria-current="page"><?php echo esc_html( $current ); ?></li>
	</ol>
</nav>
