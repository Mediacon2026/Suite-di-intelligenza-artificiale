<?php
/**
 * Responsive data table presentation.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;
$caption = isset( $args['caption'] ) ? (string) $args['caption'] : '';
$headers = isset( $args['headers'] ) && is_array( $args['headers'] ) ? $args['headers'] : array();
$rows    = isset( $args['rows'] ) && is_array( $args['rows'] ) ? $args['rows'] : array();
if ( ! $headers ) {
	return;
}
?>
<div class="table-scroll" tabindex="0"><table>
<?php
if ( '' !== $caption ) :
	?>
	<caption><?php echo esc_html( $caption ); ?></caption><?php endif; ?><thead><tr>
	<?php
	foreach ( $headers as $header ) :
		?>
	<th scope="col"><?php echo esc_html( (string) $header ); ?></th><?php endforeach; ?></tr></thead><tbody>
	<?php
	foreach ( $rows as $row ) :
		?>
	<tr>
		<?php
		foreach ( (array) $row as $cell ) :
			?>
	<td><?php echo wp_kses_post( (string) $cell ); ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
