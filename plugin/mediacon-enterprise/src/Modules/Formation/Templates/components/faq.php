<?php
/**
 * Accessible formation FAQ accordion.
 *
 * @package MediaconEnterprise
 * @var array<int,array{question:string,answer:string}> $items FAQ items.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="me-formation__section me-formation__section--muted" aria-labelledby="me-formation-faq-title">
	<div class="me-formation__container me-formation__measure">
		<h2 id="me-formation-faq-title"><?php echo esc_html__( 'Domande frequenti', 'mediacon-enterprise' ); ?></h2>
		<div class="me-formation__faq" data-formation-accordion>
			<?php foreach ( $items as $index => $item ) : ?>
				<?php $panel_id = 'me-formation-faq-' . (string) $index; ?>
				<div class="me-formation__faq-item">
					<h3><button type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>"><?php echo esc_html( $item['question'] ); ?><span aria-hidden="true">+</span></button></h3>
					<div id="<?php echo esc_attr( $panel_id ); ?>" class="me-formation__faq-panel" hidden><p><?php echo esc_html( $item['answer'] ); ?></p></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
