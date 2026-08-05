<?php
/**
 * Public Preventivo page shell.
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;
$controller = mediacon_enterprise()->get( \Mediacon\Enterprise\Modules\Preventivo\Controllers\TemplateController::class );
$controller->render();
