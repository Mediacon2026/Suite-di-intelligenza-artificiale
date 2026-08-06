<?php
/**
 * Site footer.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer">
	<div class="container site-footer__grid">
		<section>
			<h2 class="site-footer__brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
			<p><?php esc_html_e( 'Organismo di Mediazione n. 707', 'mediacon-one' ); ?><br><?php esc_html_e( 'Ente di Formazione n. 422', 'mediacon-one' ); ?></p>
		</section>
		<section>
			<h2><?php esc_html_e( 'Sedi', 'mediacon-one' ); ?></h2>
			<ul>
			<?php foreach ( mediacon_one_offices() as $office ) : ?>
				<li><?php echo esc_html( (string) ( $office['name'] ?? '' ) ); ?></li>
			<?php endforeach; ?>
			</ul>
		</section>
		<section>
			<h2><?php esc_html_e( 'Informazioni', 'mediacon-one' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'site-footer__menu',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
			<a href="<?php echo esc_url( mediacon_one_page_url( array( 'contatti' ) ) ); ?>"><?php esc_html_e( 'Contatti', 'mediacon-one' ); ?></a>
			<span aria-hidden="true"> · </span><a href="<?php echo esc_url( mediacon_one_page_url( array( 'privacy', 'privacy-policy' ) ) ); ?>"><?php esc_html_e( 'Privacy', 'mediacon-one' ); ?></a>
		</section>
		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<div class="site-footer__widgets"><?php dynamic_sidebar( 'footer-1' ); ?></div>
		<?php elseif ( has_nav_menu( 'social' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Canali social', 'mediacon-one' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'social',
					'container'      => false,
					'menu_class'     => 'social-menu',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
								</nav>
		<?php endif; ?>
	</div>
	<div class="container site-footer__legal">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
