<?php
/**
 * Theme supports, menus, and widget areas.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

/** Register theme supports and navigation locations. */
function mediacon_one_setup(): void {
	load_theme_textdomain( 'mediacon-one', MEDIACON_ONE_PATH . 'languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_editor_style( array( 'assets/css/tokens.css', 'assets/css/base.css', 'assets/css/components.css' ) );
	register_nav_menus(
		array(
			'primary' => __( 'Navigazione principale', 'mediacon-one' ),
			'mega'    => __( 'Mega menu istituzionale', 'mediacon-one' ),
			'footer'  => __( 'Link istituzionali footer', 'mediacon-one' ),
			'social'  => __( 'Link social', 'mediacon-one' ),
		)
	);
	add_image_size( 'mediacon-one-card', 720, 450, true );
}
add_action( 'after_setup_theme', 'mediacon_one_setup' );

/** Set the readable content width. */
function mediacon_one_content_width(): void {
	$GLOBALS['content_width'] = (int) apply_filters( 'mediacon_one_content_width', 840 );
}
add_action( 'after_setup_theme', 'mediacon_one_content_width', 0 );

/** Register optional widget areas without creating content. */
function mediacon_one_widgets_init(): void {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'mediacon-one' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Widget contestuali per pagine e archivi.', 'mediacon-one' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget__title">',
			'after_title'   => '</h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Footer', 'mediacon-one' ),
			'id'            => 'footer-1',
			'description'   => __( 'Informazioni aggiuntive nel footer.', 'mediacon-one' ),
			'before_widget' => '<section id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="footer-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'mediacon_one_widgets_init' );

/**
 * Add stable body hooks for layout and optional Enterprise state.
 *
 * @param array<int,string> $classes WordPress body classes.
 */
function mediacon_one_body_classes( array $classes ): array {
	$classes[] = 'mediacon-one';
	$classes[] = mediacon_one_enterprise_active() ? 'has-mediacon-enterprise' : 'has-wordpress-fallback';
	return $classes;
}
add_filter( 'body_class', 'mediacon_one_body_classes' );
