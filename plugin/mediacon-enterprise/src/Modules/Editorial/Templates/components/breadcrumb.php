<?php
/**
 * Editorial breadcrumb.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<nav class="me-editorial-breadcrumb" aria-label="<?php esc_attr_e( 'Percorso di navigazione', 'mediacon-enterprise' ); ?>">
	<div class="me-editorial-breadcrumb__inner"><ol><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'mediacon-enterprise' ); ?></a></li><li aria-current="page"><?php echo esc_html( $current ); ?></li></ol></div>
</nav>
