<?php
/**
 * Public search results.
 *
 * @package MediaconEnterprise
 *
 * @var array<string,mixed>|null $result Search result.
 * @var string                   $error Error message.
 * @var array<string,mixed>      $config Search configuration.
 * @var array<string,mixed>      $input Request values.
 * @var \Mediacon\Enterprise\Modules\Search\Support\Highlighter $highlighter Highlighter.
 */

defined( 'ABSPATH' ) || exit;
$results_url   = (string) get_permalink( absint( $config['page_id'] ?? 0 ) );
$search_action = $results_url;
$filter_labels = array(
	'all'           => 'Tutto',
	'mediation'     => 'Mediazione',
	'formation'     => 'Formazione',
	'blog'          => 'Blog',
	'jurisprudence' => 'Sentenze',
	'legislation'   => 'Normativa',
	'insights'      => 'Approfondimenti',
	'courses'       => 'Corsi',
	'faq'           => 'FAQ',
);
?>
<main class="mce-search">
	<header class="mce-search__hero"><p class="mce-search__eyebrow"><?php esc_html_e( 'Mediacon', 'mediacon-enterprise' ); ?></p><h1><?php esc_html_e( 'Ricerca nel sito', 'mediacon-enterprise' ); ?></h1><?php require __DIR__ . '/search-form.php'; ?></header>
	<?php
	if ( '' !== $error ) :
		?>
		<p class="mce-search__notice" role="alert"><?php echo esc_html( $error ); ?></p><?php endif; ?>
	<?php if ( is_array( $result ) ) : ?>
		<form class="mce-search__filters" method="get" action="<?php echo esc_url( $results_url ); ?>">
			<input type="hidden" name="q" value="<?php echo esc_attr( (string) $result['query'] ); ?>">
			<label><?php esc_html_e( 'Area', 'mediacon-enterprise' ); ?><select name="type">
			<?php
			foreach ( $filter_labels as $key => $label ) :
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( (string) ( $input['type'] ?? 'all' ), $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label>
			<label><?php esc_html_e( 'Ordina', 'mediacon-enterprise' ); ?><select name="order"><option value="relevance" <?php selected( (string) ( $input['order'] ?? 'relevance' ), 'relevance' ); ?>><?php esc_html_e( 'Rilevanza', 'mediacon-enterprise' ); ?></option><option value="date" <?php selected( (string) ( $input['order'] ?? '' ), 'date' ); ?>><?php esc_html_e( 'Data', 'mediacon-enterprise' ); ?></option></select></label>
			<label><?php esc_html_e( 'Anno', 'mediacon-enterprise' ); ?><input type="number" name="year" min="2000" max="<?php echo esc_attr( wp_date( 'Y' ) ); ?>" value="<?php echo esc_attr( (string) ( $input['year'] ?? '' ) ); ?>"></label>
			<button type="submit"><?php esc_html_e( 'Applica filtri', 'mediacon-enterprise' ); ?></button>
		</form>
		<p class="mce-search__count" aria-live="polite"><?php echo esc_html( sprintf( /* translators: %d is the result count. */ _n( '%d risultato', '%d risultati', (int) $result['total'], 'mediacon-enterprise' ), (int) $result['total'] ) ); ?></p>
		<?php
		if ( array() === $result['items'] ) :
			?>
			<div class="mce-search__empty"><h2><?php esc_html_e( 'Nessun risultato', 'mediacon-enterprise' ); ?></h2><p><?php esc_html_e( 'Prova un sinonimo, riduci i filtri o usa termini più generali.', 'mediacon-enterprise' ); ?></p></div><?php endif; ?>
		<div class="mce-search__grid">
			<?php foreach ( $result['items'] as $item ) : ?>
				<article class="mce-search-card">
					<?php
					if ( '' !== $item['image'] ) :
						?>
						<img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy"><?php endif; ?>
					<div class="mce-search-card__body"><p class="mce-search-card__meta"><?php echo esc_html( $item['label'] ); ?> · <?php echo esc_html( $item['area'] ); ?>
					<?php
					if ( '' !== $item['date'] ) :
						?>
						· <time><?php echo esc_html( $item['date'] ); ?></time><?php endif; ?></p>
					<h2><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo wp_kses( $highlighter->highlight( $item['title'], $result['terms'] ), array( 'mark' => array() ) ); ?></a></h2>
					<p><?php echo wp_kses( $highlighter->highlight( $item['excerpt'], $result['terms'] ), array( 'mark' => array() ) ); ?></p></div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		if ( 1 < $result['pages'] ) :
			?>
			<nav class="mce-search__pagination" aria-label="<?php esc_attr_e( 'Pagine dei risultati', 'mediacon-enterprise' ); ?>">
			<?php
			for ( $page_number = 1; $page_number <= $result['pages']; ++$page_number ) :
						$url = add_query_arg( array_merge( $input, array( 'search_page' => $page_number ) ), $results_url );
				?>
	<a href="<?php echo esc_url( $url ); ?>" 
				<?php
				if ( $page_number === $result['page'] ) :
					?>
			aria-current="page"<?php endif; ?>><?php echo esc_html( (string) $page_number ); ?></a><?php endfor; ?></nav><?php endif; ?>
		<?php
		if ( array() !== $result['suggestions'] ) :
			?>
			<aside class="mce-search__related"><h2><?php esc_html_e( 'Potrebbe esserti utile', 'mediacon-enterprise' ); ?></h2>
			<?php
			foreach ( $result['suggestions'] as $suggestion ) :
				?>
						<?php
						if ( '' !== $suggestion['url'] ) :
							?>
	<a href="<?php echo esc_url( $suggestion['url'] ); ?>"><?php echo esc_html( $suggestion['label'] ); ?></a>
							<?php
			else :
				?>
	<p><?php echo esc_html( $suggestion['label'] ); ?></p><?php endif; ?><?php endforeach; ?></aside><?php endif; ?>
	<?php endif; ?>
</main>
