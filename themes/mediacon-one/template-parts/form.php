<?php
/**
 * Presentation wrapper for an existing WordPress or plugin form.
 *
 * The theme does not process or persist form data.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$form_title   = isset( $args['title'] ) ? (string) $args['title'] : '';
$form_content = isset( $args['content'] ) ? (string) $args['content'] : '';
if ( '' === $form_content ) {
	return;
}
?>
<section class="form-panel">
<?php
if ( '' !== $form_title ) :
	?>
	<h2><?php echo esc_html( $form_title ); ?></h2><?php endif; ?><div class="form-panel__content"><?php echo wp_kses_post( $form_content ); ?></div></section>
