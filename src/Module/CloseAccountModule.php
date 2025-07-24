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

use Contao\FilesModel;
use Contao\Folder;
use Contao\Idna;
use Contao\MemberModel;
use Contao\ModuleCloseAccount;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;

class CloseAccountModule extends ModuleCloseAccount
{
    public function compile(): void
    {
        $user = System::getContainer()->get('security.helper')->getUser();
        $objMember = MemberModel::findById($user->id);
        $isSingleSignOn = false;
        $container = System::getContainer();

        foreach (['apple', 'linkedin', 'google'] as $service) {
            if ($user->{'bifroest_'.$service.'_sub'}) {
                $isSingleSignOn = true;
            }
        }

        if ($isSingleSignOn) {
            $strFormId = 'tl_close_account_'.$this->id;
            $this->Template->fields = '';
            $this->Template->formId = $strFormId;
            $this->Template->slabel = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['closeAccount']);

            $request = $container->get('request_stack')->getCurrentRequest();

            if ('POST' === $request->getMethod() && $request->request->get('FORM_SUBMIT') === $strFormId) {
                // HOOK: send account ID
                if (isset($GLOBALS['TL_HOOKS']['closeAccount']) && \is_array($GLOBALS['TL_HOOKS']['closeAccount'])) {
                    foreach ($GLOBALS['TL_HOOKS']['closeAccount'] as $callback) {
                        System::importStatic($callback[0])->{$callback[1]}($user->id, $this->reg_close, $this);
                    }
                }

                // Remove the account
                if ('close_delete' == $this->reg_close) {
                    if ($this->reg_deleteDir && $objMember->assignDir && ($filesModel = FilesModel::findByUuid($objMember->homeDir))) {
                        $folder = new Folder($filesModel->path);
                        $folder->delete();
                    }

                    $objMember->delete();

                    $container->get('monolog.logger.contao.access')->info('User account ID '.$user->id.' ('.Idna::decodeEmail($user->email).') has been deleted');
                }
                // Deactivate the account
                else {
                    $objMember->disable = 1;
                    $objMember->tstamp = time();
                    $objMember->save();

                    $container->get('monolog.logger.contao.access')->info('User account ID '.$user->id.' ('.Idna::decodeEmail($user->email).') has been deactivated');
                }

                // Log out the user (see #93)
                $container->get('security.token_storage')->setToken();
                $container->get('request_stack')->getSession()->invalidate();

                // Check whether there is a jumpTo page
                if ($objJumpTo = PageModel::findById($this->objModel->jumpTo)) {
                    $this->jumpToOrReload($objJumpTo->row());
                }

                $this->reload();
            }
        } else {
            parent::compile();
        }
    }
}
