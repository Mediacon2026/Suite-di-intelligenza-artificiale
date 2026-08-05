<?php
/**
 * Public search page shell.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$controller = mediacon_enterprise()->get( \Mediacon\Enterprise\Modules\Search\Controllers\TemplateController::class );
$controller->render();
