<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoBifroestLogin\Module;

use Contao\ModuleChangePassword;
use Contao\System;

class ChangePasswordModule extends ModuleChangePassword
{
    public function generate()
    {
        $user = System::getContainer()->get('security.helper')->getUser();

        $isSingleSignOn = false;

        foreach (['apple', 'linkedin', 'google'] as $service) {
            if ($user->{'bifroest_'.$service.'_sub'}) {
                $isSingleSignOn = true;
            }
        }

        if ($isSingleSignOn) {
            return $GLOBALS['TL_LANG']['MSC']['bifroest']['changePassword'];
        }

        return parent::generate();
    }
}
