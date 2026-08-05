<?php
/**
 * Information card grid component.
 *
 * @package MediaconEnterprise
 * @var array<int,string> $items Card text.
 * @var string            $title Section title.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="me-mediation__section" aria-labelledby="me-cards-title">
	<div class="me-mediation__container">
		<h2 id="me-cards-title"><?php echo esc_html( $title ); ?></h2>
		<div class="me-mediation__cards">
			<?php foreach ( $items as $index => $item ) : ?>
				<article class="me-mediation__card">
					<span class="me-mediation__card-index" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
					<p><?php echo esc_html( $item ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
