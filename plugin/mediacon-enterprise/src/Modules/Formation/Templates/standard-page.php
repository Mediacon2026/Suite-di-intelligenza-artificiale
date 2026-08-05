<?php
/**
 * Standard public formation page.
 *
 * @package MediaconEnterprise
 * @var array<string,mixed> $content Page content.
 * @var array<int,array<string,string>> $faq FAQ items.
 * @var \Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController $components Component renderer.
 */

defined( 'ABSPATH' ) || exit;

$components->renderComponent( 'hero', $content );
$components->renderComponent( 'breadcrumb', array( 'current' => $content['title'] ) );
?>
<main id="main-content" class="me-formation">
	<?php
	if ( have_posts() ) :
		?>
		<section class="me-formation__section"><div class="me-formation__container me-formation__measure me-formation__prose">
		<?php
		while ( have_posts() ) {
				the_post();
				the_content(); }
		?>
</div></section><?php endif; ?>
	<?php $components->renderComponent( 'faq', array( 'items' => $faq ) ); ?>
</main>
