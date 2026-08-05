<?php
/**
 * Page hero.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$eyebrow    = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : get_bloginfo( 'name' );
$hero_title = isset( $args['title'] ) ? (string) $args['title'] : get_the_title();
?>
<header class="page-hero"><div class="container page-hero__inner"><p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p><h1><?php echo esc_html( $hero_title ); ?></h1>
<?php
if ( has_excerpt() ) :
	?>
	<p class="page-hero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?></div></header>
