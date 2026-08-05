<?php
/**
 * WordPress template entrypoint for enabled mediation pages.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Core\Plugin;
use Mediacon\Enterprise\Modules\Mediation\Controllers\TemplateController;

defined( 'ABSPATH' ) || exit;

Plugin::instance()->container()->get( TemplateController::class )->renderCurrent();
