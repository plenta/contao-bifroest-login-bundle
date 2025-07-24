<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

use Plenta\ContaoBifroestLogin\Module\ChangePasswordModule;
use Plenta\ContaoBifroestLogin\Module\CloseAccountModule;

$GLOBALS['FE_MOD']['user']['changePassword'] = ChangePasswordModule::class;
$GLOBALS['FE_MOD']['user']['closeAccount'] = CloseAccountModule::class;
