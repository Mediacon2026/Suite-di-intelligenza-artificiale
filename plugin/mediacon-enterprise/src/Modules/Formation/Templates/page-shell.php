<?php
/**
 * WordPress template entrypoint for enabled formation content.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Core\Plugin;
use Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController;

defined( 'ABSPATH' ) || exit;

Plugin::instance()->container()->get( TemplateController::class )->renderCurrent();
