<?php
/**
 * Search administration form.
 *
 * @package MediaconEnterprise
 *
 * @var array<string,mixed> $config Search configuration.
 */

defined( 'ABSPATH' ) || exit;
$content_labels = array(
	'pages'         => 'Pagine WordPress',
	'blog'          => 'Blog',
	'jurisprudence' => 'Sentenze',
	'legislation'   => 'Normativa',
	'insights'      => 'Approfondimenti',
	'courses'       => 'Corsi',
	'teachers'      => 'Docenti',
	'faq'           => 'FAQ',
	'mediation'     => 'Pagine Mediazione',
	'formation'     => 'Pagine Formazione',
);
$synonym_lines  = array_map( static fn ( array $group ): string => implode( ', ', $group ), $config['synonyms'] ?? array() );
?>
<div class="wrap mce-search-admin"><h1><?php esc_html_e( 'Mediacon — Ricerca', 'mediacon-enterprise' ); ?></h1>
<?php
if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only status flag.
	?>
	<div class="notice notice-success"><p><?php esc_html_e( 'Impostazioni salvate e cache invalidata.', 'mediacon-enterprise' ); ?></p></div><?php endif; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="mediacon_enterprise_save_search"><?php wp_nonce_field( 'mediacon_enterprise_save_search', 'mediacon_enterprise_search_nonce' ); ?>
<h2><?php esc_html_e( 'Stato e pagina risultati', 'mediacon-enterprise' ); ?></h2><label><input type="checkbox" name="search[frontend_enabled]" value="1" <?php checked( ! empty( $config['frontend_enabled'] ) ); ?>> <?php esc_html_e( 'Frontend attivo', 'mediacon-enterprise' ); ?></label><p><label><?php esc_html_e( 'ID pagina risultati', 'mediacon-enterprise' ); ?> <input type="number" min="0" name="search[page_id]" value="<?php echo esc_attr( (string) ( $config['page_id'] ?? 0 ) ); ?>"></label></p>
<h2><?php esc_html_e( 'Contenuti inclusi', 'mediacon-enterprise' ); ?></h2>
<?php
foreach ( $content_labels as $key => $label ) :
	?>
	<label><input type="checkbox" name="search[included][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $config['included'][ $key ] ) ); ?>> <?php echo esc_html( $label ); ?></label><?php endforeach; ?>
<p><label><?php esc_html_e( 'ID contenuti esclusi, separati da virgola', 'mediacon-enterprise' ); ?><input class="large-text" name="search[excluded_text]" value="<?php echo esc_attr( implode( ',', $config['excluded_ids'] ?? array() ) ); ?>"></label></p>
<h2><?php esc_html_e( 'Limiti', 'mediacon-enterprise' ); ?></h2>
<?php
foreach ( array(
	'per_page'         => 'Risultati per pagina',
	'suggestion_limit' => 'Suggerimenti (massimo 10)',
	'min_chars'        => 'Caratteri minimi',
	'max_chars'        => 'Caratteri massimi',
	'max_candidates'   => 'Candidati massimi',
	'rate_limit'       => 'Richieste autocomplete/minuto',
) as $key => $label ) :
	?>
				<label><?php echo esc_html( $label ); ?> <input type="number" min="1" name="search[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) ( $config[ $key ] ?? 0 ) ); ?>"></label><?php endforeach; ?>
<h2><?php esc_html_e( 'Sinonimi', 'mediacon-enterprise' ); ?></h2><p><?php esc_html_e( 'Un gruppo per riga, termini separati da virgola.', 'mediacon-enterprise' ); ?></p><textarea class="large-text code" rows="8" name="search[synonyms_text]"><?php echo esc_textarea( implode( "\n", $synonym_lines ) ); ?></textarea>
<h2><?php esc_html_e( 'Priorità contenuti', 'mediacon-enterprise' ); ?></h2>
<?php
foreach ( $config['priorities'] ?? array() as $key => $priority ) :
	?>
	<label><?php echo esc_html( ucfirst( $key ) ); ?> <input type="number" min="-20" max="20" name="search[priorities][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $priority ); ?>"></label><?php endforeach; ?>
<h2><?php esc_html_e( 'Autocomplete e cache', 'mediacon-enterprise' ); ?></h2><label><input type="checkbox" name="search[autocomplete]" value="1" <?php checked( ! empty( $config['autocomplete'] ) ); ?>> <?php esc_html_e( 'Autocomplete attivo', 'mediacon-enterprise' ); ?></label><label><input type="checkbox" name="search[cache_enabled]" value="1" <?php checked( ! empty( $config['cache_enabled'] ) ); ?>> <?php esc_html_e( 'Cache attiva', 'mediacon-enterprise' ); ?></label><label><?php esc_html_e( 'Durata cache (secondi)', 'mediacon-enterprise' ); ?> <input type="number" min="60" name="search[cache_ttl]" value="<?php echo esc_attr( (string) ( $config['cache_ttl'] ?? 300 ) ); ?>"></label><?php submit_button(); ?></form></div>
