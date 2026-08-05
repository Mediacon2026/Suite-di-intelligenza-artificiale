<?php
/**
 * Filterable public course archive.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var array<string,string> $course_types Course types.
 * @var \WP_Query $query Course query.
 * @var \Mediacon\Enterprise\Modules\Formation\Services\CourseRepository $repository Course repository.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$selected_search = filter_input( INPUT_GET, 'formation_search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_type   = filter_input( INPUT_GET, 'formation_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_mode   = filter_input( INPUT_GET, 'formation_mode', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_status = filter_input( INPUT_GET, 'formation_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_from   = filter_input( INPUT_GET, 'formation_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$selected_to     = filter_input( INPUT_GET, 'formation_to', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-formation">
	<section class="me-formation__section">
		<div class="me-formation__container">
			<form class="me-formation__filters" method="get" action="<?php echo esc_url( get_permalink() ); ?>" role="search">
				<label><span><?php echo esc_html__( 'Cerca', 'mediacon-enterprise' ); ?></span><input type="search" name="formation_search" value="<?php echo esc_attr( is_string( $selected_search ) ? $selected_search : '' ); ?>"></label>
				<label><span><?php echo esc_html__( 'Tipologia', 'mediacon-enterprise' ); ?></span><select name="formation_type"><option value=""><?php echo esc_html__( 'Tutte', 'mediacon-enterprise' ); ?></option>
				<?php
				foreach ( $course_types as $key => $label ) :
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_type, $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label>
				<label><span><?php echo esc_html__( 'Modalità', 'mediacon-enterprise' ); ?></span><select name="formation_mode"><option value=""><?php echo esc_html__( 'Tutte', 'mediacon-enterprise' ); ?></option><option value="online" <?php selected( $selected_mode, 'online' ); ?>><?php echo esc_html__( 'Online', 'mediacon-enterprise' ); ?></option><option value="presenza" <?php selected( $selected_mode, 'presenza' ); ?>><?php echo esc_html__( 'In presenza', 'mediacon-enterprise' ); ?></option><option value="ibrida" <?php selected( $selected_mode, 'ibrida' ); ?>><?php echo esc_html__( 'Ibrida', 'mediacon-enterprise' ); ?></option></select></label>
				<label><span><?php echo esc_html__( 'Stato', 'mediacon-enterprise' ); ?></span><select name="formation_status"><option value=""><?php echo esc_html__( 'Tutti', 'mediacon-enterprise' ); ?></option><option value="iscrizioni-aperte" <?php selected( $selected_status, 'iscrizioni-aperte' ); ?>><?php echo esc_html__( 'Iscrizioni aperte', 'mediacon-enterprise' ); ?></option><option value="posti-esauriti" <?php selected( $selected_status, 'posti-esauriti' ); ?>><?php echo esc_html__( 'Posti esauriti', 'mediacon-enterprise' ); ?></option><option value="concluso" <?php selected( $selected_status, 'concluso' ); ?>><?php echo esc_html__( 'Concluso', 'mediacon-enterprise' ); ?></option></select></label>
				<label><span><?php echo esc_html__( 'Dal', 'mediacon-enterprise' ); ?></span><input type="date" name="formation_from" value="<?php echo esc_attr( is_string( $selected_from ) ? $selected_from : '' ); ?>"></label>
				<label><span><?php echo esc_html__( 'Al', 'mediacon-enterprise' ); ?></span><input type="date" name="formation_to" value="<?php echo esc_attr( is_string( $selected_to ) ? $selected_to : '' ); ?>"></label>
				<button class="me-formation__button" type="submit"><?php echo esc_html__( 'Filtra', 'mediacon-enterprise' ); ?></button>
			</form>
			<?php
			$components->renderComponent(
				'course-grid',
				array(
					'query'      => $query,
					'repository' => $repository,
					'components' => $components,
					'paginate'   => true,
				)
			);
			?>
		</div>
	</section>
</main>
