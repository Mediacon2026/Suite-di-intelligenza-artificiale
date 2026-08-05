<?php
/**
 * Editorial template entrypoint.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Core\Plugin;
use Mediacon\Enterprise\Modules\Editorial\Controllers\TemplateController;

defined( 'ABSPATH' ) || exit;

Plugin::instance()->container()->get( TemplateController::class )->renderCurrent();
