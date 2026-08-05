<?php
/**
 * Non-destructive single editorial article template.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;

while ( have_posts() ) :
	the_post();
	$card       = $cards->present( $post_id );
	$share_url  = rawurlencode( $card['url'] );
	$share_text = rawurlencode( $card['title'] );
	$components->renderComponent(
		'hero',
		array(
			'title'       => get_the_title(),
			'description' => $card['category'] . ( $card['category'] && $card['date'] ? ' · ' : '' ) . $card['date'],
		)
	);
	$components->renderComponent( 'breadcrumb', array( 'current' => get_the_title() ) );
	?>
	<main id="main-content" class="me-editorial">
		<article class="me-editorial-article">
			<div class="me-editorial__container me-editorial__measure">
				<?php
				if ( has_post_thumbnail() ) :
					?>
					<figure class="me-editorial-article__image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?></figure><?php endif; ?>
				<div class="me-editorial-article__content"><?php the_content(); ?></div>
				<nav class="me-editorial-article__share" aria-label="<?php esc_attr_e( 'Condividi articolo', 'mediacon-enterprise' ); ?>">
					<strong><?php esc_html_e( 'Condividi', 'mediacon-enterprise' ); ?></strong>
					<a href="mailto:?subject=<?php echo esc_attr( $share_text ); ?>&amp;body=<?php echo esc_attr( $share_url ); ?>"><?php esc_html_e( 'Email', 'mediacon-enterprise' ); ?></a>
					<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $share_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'LinkedIn', 'mediacon-enterprise' ); ?></a>
					<button type="button" data-editorial-copy-url="<?php echo esc_url( $card['url'] ); ?>"><?php esc_html_e( 'Copia link', 'mediacon-enterprise' ); ?></button><span class="screen-reader-text" data-editorial-copy-status aria-live="polite"></span>
				</nav>
				<nav class="me-editorial-article__navigation" aria-label="<?php esc_attr_e( 'Articoli adiacenti', 'mediacon-enterprise' ); ?>"><div><?php previous_post_link( '%link', '← %title' ); ?></div><div><?php next_post_link( '%link', '%title →' ); ?></div></nav>
			</div>
		</article>
		<?php
		if ( $related->have_posts() ) :
			?>
			<section class="me-editorial__section me-editorial__section--muted" aria-labelledby="me-related-title"><div class="me-editorial__container"><h2 id="me-related-title"><?php esc_html_e( 'Articoli correlati', 'mediacon-enterprise' ); ?></h2><div class="me-editorial__grid">
			<?php
			while ( $related->have_posts() ) :
				?>
						<?php $related->the_post(); ?><?php $components->renderComponent( 'card', array( 'card' => $cards->present( get_the_ID() ) ) ); ?><?php endwhile; ?></div><?php wp_reset_postdata(); ?></div></section><?php endif; ?>
		<?php
		$components->renderComponent(
			'cta',
			array(
				'title'       => __( 'Continua a esplorare', 'mediacon-enterprise' ),
				'description' => __( 'Consulta gli altri contributi e aggiornamenti pubblicati da Mediacon.', 'mediacon-enterprise' ),
				'label'       => __( 'Vai al blog', 'mediacon-enterprise' ),
				'url'         => home_url( '/blog/' ),
			)
		);
		?>
	</main>
	<?php
endwhile;
