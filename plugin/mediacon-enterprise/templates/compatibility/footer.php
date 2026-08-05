<?php
/**
 * Design Core-compatible footer component.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="mdc-footer">
	<div class="mdc-container">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
	</div>
</footer>
