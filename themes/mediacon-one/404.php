<?php
/**
 * Not found template.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main error-page"><div class="container container--reading"><p class="eyebrow">404</p><h1><?php esc_html_e( 'Pagina non trovata', 'mediacon-one' ); ?></h1><p><?php esc_html_e( 'La risorsa richiesta non è disponibile. Puoi cercare nel sito oppure tornare alla pagina iniziale.', 'mediacon-one' ); ?></p><?php get_search_form(); ?><p><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Torna alla home', 'mediacon-one' ); ?></a></p></div></main>
<?php get_footer(); ?>
