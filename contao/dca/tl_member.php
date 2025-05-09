<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

$GLOBALS['TL_DCA']['tl_member']['fields']['bifroest_linkedin_sub'] = [
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['bifroest_google_sub'] = [
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['bifroest_apple_sub'] = [
    'sql' => "varchar(255) NOT NULL default ''",
];
