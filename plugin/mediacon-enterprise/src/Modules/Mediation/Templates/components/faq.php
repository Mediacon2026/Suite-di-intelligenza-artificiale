<?php
/**
 * Accessible FAQ accordion component.
 *
 * @package MediaconEnterprise
 * @var array<int,array{question:string,answer:string}> $items FAQ entries.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="me-mediation__section me-mediation__section--muted" aria-labelledby="me-faq-title">
	<div class="me-mediation__container me-mediation__measure">
		<h2 id="me-faq-title"><?php echo esc_html__( 'Domande frequenti', 'mediacon-enterprise' ); ?></h2>
		<div class="me-mediation__faq" data-mediation-accordion>
			<?php foreach ( $items as $index => $item ) : ?>
				<?php $panel_id = 'me-faq-panel-' . (string) $index; ?>
				<div class="me-mediation__faq-item">
					<h3>
						<button type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
							<?php echo esc_html( $item['question'] ); ?>
							<span aria-hidden="true">+</span>
						</button>
					</h3>
					<div id="<?php echo esc_attr( $panel_id ); ?>" class="me-mediation__faq-panel" hidden>
						<p><?php echo esc_html( $item['answer'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
