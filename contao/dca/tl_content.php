<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

use Plenta\ContaoBifroestLogin\Controller\Contao\ContentElement\BifroestLoginController;

$GLOBALS['TL_DCA']['tl_content']['palettes'][BifroestLoginController::TYPE] = '
{type_legend},type,headline;
{config_legend},bifroest_services,module,bifroest_jumpTo,bifroest_jumpTo_emailInUse,bifroest_friendly_forwarding;
{text_legend},text;
{layout_linkedin_legend},bifroest_linkedin_icon_size,bifroest_linkedin_icon_type;
{layout_google_legend},bifroest_google_icon_type,bifroest_google_icon_theme,bifroest_google_icon_size,bifroest_google_icon_shape,bifroest_google_icon_text,bifroest_google_icon_alignment,bifroest_google_icon_width;
{layout_apple_legend},bifroest_apple_icon_mode,bifroest_apple_icon_type,bifroest_apple_icon_color,bifroest_apple_icon_border,bifroest_apple_icon_border_radius,bifroest_apple_icon_width,bifroest_apple_icon_height,bifroest_apple_icon_size,bifroest_apple_icon_logo_position,bifroest_apple_icon_label_position;
{template_legend:hide},customTpl;
{protected_legend:hide},protected;
{expert_legend:hide},cssID;
{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_services'] = [
    'inputType' => 'checkboxWizard',
    'eval' => ['multiple' => true],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_services_options'],
    'sql' => 'mediumtext NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_jumpTo'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_jumpTo_emailInUse'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_linkedin_icon_size'] = [
    'inputType' => 'select',
    'options' => ['small', 'large'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_sizes_options'],
    'eval' => ['tl_class' => 'clr w50'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'small'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_linkedin_icon_type'] = [
    'inputType' => 'select',
    'options' => ['retina' => 'Retina', 'non-retina' => 'Non-Retina'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'retina'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_google_icon_type'] = [
    'inputType' => 'select',
    'options' => ['standard', 'icon'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_type_options'],
    'eval' => ['tl_class' => 'clr w25'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'standard'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_google_icon_theme'] = [
    'inputType' => 'select',
    'options' => ['outline', 'filled_blue', 'filled_black'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_theme_options'],
    'eval' => ['tl_class' => 'w25'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'outline'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_google_icon_size'] = [
    'inputType' => 'select',
    'options' => ['small', 'medium', 'large'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_sizes_options'],
    'eval' => ['tl_class' => 'w25'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'large'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_google_icon_shape'] = [
    'inputType' => 'select',
    'options' => ['rectangular', 'pill', 'circle', 'square'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_shape_options'],
    'eval' => ['tl_class' => 'w25'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'rectangular'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_google_icon_text'] = [
    'inputType' => 'select',
    'options' => ['signin_with', 'signup_with', 'continue_with', 'signin'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_text_options'],
    'eval' => ['tl_class' => 'w25'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'signin_with'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_google_icon_alignment'] = [
    'inputType' => 'select',
    'options' => ['left', 'center'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_alignment_options'],
    'eval' => ['tl_class' => 'w25'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'left'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_google_icon_width'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'tl_class' => 'w25', 'maxval' => 400],
    'sql' => 'int(10) unsigned NOT NULL default 200',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_mode'] = [
    'inputType' => 'select',
    'options' => ['center-align', 'left-align', 'logo-only'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_alignment_options'],
    'eval' => ['tl_class' => 'clr w33'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'center-align'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_size'] = [
    'inputType' => 'select',
    'options' => ['small', 'medium', 'large'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_sizes_options'],
    'eval' => ['tl_class' => 'w33'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'small'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_type'] = [
    'inputType' => 'select',
    'options' => ['sign-in', 'continue', 'sign-up'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_text_options'],
    'eval' => ['tl_class' => 'w33'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'sign-in'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_color'] = [
    'inputType' => 'select',
    'options' => ['black', 'white'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['bifroest_icon_color_options'],
    'eval' => ['tl_class' => 'w33'],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default 'black'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_border'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => [
        'type' => 'boolean',
        'default' => true,
    ],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_border_radius'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50', 'maxval' => 50, 'minval' => 0],
    'sql' => 'int(10) unsigned NOT NULL default 15',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_width'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50', 'maxval' => 375, 'minval' => 130],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_height'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50', 'maxval' => 64, 'minval' => 30],
    'sql' => "varchar(16) COLLATE ascii_bin NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_logo_position'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'tl_class' => 'w33'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_apple_icon_label_position'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'tl_class' => 'w33'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['bifroest_friendly_forwarding'] = [
    'inputType' => 'checkbox',
    'sql' => ['type' => 'boolean', 'default' => false],
];
