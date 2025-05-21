<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoBifroestLogin\UserManager;

use Contao\CoreBundle\Security\User\UserChecker;
use Contao\FilesModel;
use Contao\Folder;
use Contao\MemberModel;
use Contao\Module;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Plenta\ContaoBifroestLogin\Cookies\CookieManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Manager
{
    public function __construct(
        #[Autowire(service: 'contao.security.frontend_user_provider')]
        protected UserProviderInterface $userProvider,
        #[Autowire(service: 'contao.security.user_checker')]
        protected UserChecker $userChecker,
        protected TokenStorageInterface $tokenStorage,
        protected RequestStack $requestStack,
        protected EventDispatcherInterface $eventDispatcher,
        protected CookieManager $cookieManager,
    ) {
    }

    public function createNewUser(array $arrData, ModuleModel $module)
    {
        $arrData['tstamp'] = time();
        $arrData['login'] = $module->reg_allowLogin;
        $arrData['dateAdded'] = $arrData['tstamp'];

        // Set default groups
        if (!\array_key_exists('groups', $arrData)) {
            $arrData['groups'] = $module->reg_groups;
        }

        // Create the user
        $objNewUser = new MemberModel();
        $objNewUser->setRow($arrData);
        $objNewUser->save();

        // Store the new ID (see https://github.com/contao/contao/pull/196#discussion_r243555399)
        $arrData['id'] = $objNewUser->id;

        // Assign home directory
        if ($module->reg_assignDir) {
            $objHomeDir = FilesModel::findByUuid($module->reg_homeDir);

            if (null !== $objHomeDir) {
                $strUserDir = StringUtil::standardize($arrData['username'] ?? '') ?: 'user_'.$objNewUser->id;

                // Add the user ID if the directory exists
                while (is_dir(System::getContainer()->getParameter('kernel.project_dir').'/'.$objHomeDir->path.'/'.$strUserDir)) {
                    $strUserDir .= '_'.$objNewUser->id;
                }

                // Create the user folder
                new Folder($objHomeDir->path.'/'.$strUserDir);

                $objUserDir = FilesModel::findByPath($objHomeDir->path.'/'.$strUserDir);

                // Save the folder ID
                $objNewUser->assignDir = 1;
                $objNewUser->homeDir = $objUserDir->uuid;
                $objNewUser->save();
            }
        }

        // HOOK: send insert ID and user data
        if (isset($GLOBALS['TL_HOOKS']['createNewUser']) && \is_array($GLOBALS['TL_HOOKS']['createNewUser'])) {

            /** @var class-string<Module> $strClass */
            $strClass = Module::findClass($module->type);

            // Return if the class does not exist
            if (!class_exists($strClass))
            {
                System::getContainer()->get('monolog.logger.contao.error')->error('Module class "' . $strClass . '" (module "' . $module->type . '") does not exist');
            }

            $objModule = new $strClass($module, 'main');

            foreach ($GLOBALS['TL_HOOKS']['createNewUser'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objNewUser->id, $arrData, $objModule);
            }
        }

        // Create the initial version (see #7816)
        $objVersions = new Versions('tl_member', $objNewUser->id);
        $objVersions->setUsername($objNewUser->username);
        $objVersions->setEditUrl(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'member', 'act' => 'edit', 'id' => $objNewUser->id]));
        $objVersions->initialize();

        return $objNewUser;
    }

    public function loginUser(MemberModel $user): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $userObj = $this->userProvider->loadUserByIdentifier($user->username);
        $this->userChecker->checkPreAuth($userObj);

        $usernamePasswordToken = new UsernamePasswordToken($userObj, 'frontend', $userObj->getRoles());
        $this->tokenStorage->setToken($usernamePasswordToken);

        $event = new InteractiveLoginEvent($request, $usernamePasswordToken);
        $this->eventDispatcher->dispatch($event, 'security.interactive_login');

        $this->cookieManager
            ->removeCookie('bifroest_login_content_element')
            ->removeCookie('bifroest_login_state')
            ->removeCookie('bifroest_login_redirect')
        ;
    }
}
