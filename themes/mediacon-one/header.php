<?php
/**
 * Site header.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Vai al contenuto', 'mediacon-one' ); ?></a>
<header class="site-header" data-site-header>
	<div class="container site-header__bar">
		<div class="site-branding">
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a class="site-branding__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
		<?php endif; ?>
		</div>
		<button class="icon-button menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation" data-menu-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Apri il menu', 'mediacon-one' ); ?></span>
			<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
		</button>
		<nav id="site-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Navigazione principale', 'mediacon-one' ); ?>" data-primary-navigation>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'primary-navigation__list',
					'fallback_cb'    => 'wp_page_menu',
					'depth'          => 3,
				)
			);
			?>
		</nav>
		<div class="site-header__actions">
			<button class="icon-button search-toggle" type="button" aria-expanded="false" aria-controls="header-search" data-search-toggle>
				<span class="screen-reader-text"><?php esc_html_e( 'Apri la ricerca', 'mediacon-one' ); ?></span>
				<span aria-hidden="true">⌕</span>
			</button>
			<a class="button button--outline header-cta header-cta--course" href="<?php echo esc_url( mediacon_one_page_url( array( 'formazione', 'corsi' ) ) ); ?>"><?php esc_html_e( 'Scopri i corsi', 'mediacon-one' ); ?></a>
			<a class="button header-cta" href="<?php echo esc_url( mediacon_one_page_url( array( 'istanza-di-mediazione', 'avvia-una-mediazione', 'mediazione' ) ) ); ?>"><?php esc_html_e( 'Avvia una mediazione', 'mediacon-one' ); ?></a>
		</div>
	</div>
	<div id="header-search" class="header-search" hidden data-search-panel>
		<div class="container header-search__inner">
			<?php mediacon_one_render_search(); ?>
			<button class="text-button" type="button" data-search-close><?php esc_html_e( 'Chiudi', 'mediacon-one' ); ?></button>
		</div>
	</div>
	<?php if ( has_nav_menu( 'mega' ) ) : ?>
		<nav class="mega-navigation" aria-label="<?php esc_attr_e( 'Aree istituzionali', 'mediacon-one' ); ?>">
			<div class="container">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'mega',
					'container'      => false,
					'menu_class'     => 'mega-navigation__list',
					'depth'          => 2,
				)
			);
			?>
			</div>
		</nav>
	<?php endif; ?>
</header>
