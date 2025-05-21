<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoBifroestLogin\Controller\Authentication;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Plenta\ContaoBifroestLogin\UserManager\Manager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('_bifroest/auth/apple', defaults: ['_scope' => 'frontend'])]
class AppleController extends AbstractAuthenticationController
{
    #[Route('/callback', name: 'bifroest_apple_callback', defaults: ['_token_check' => false], methods: ['POST'])]
    public function callback(
        Request $request,
        Manager $userManager,
        ContentUrlGenerator $contentUrlGenerator
    ) {
        $state = $request->request->get('state');

        if ($state !== $request->cookies->get('bifroest_login_state')) {
            throw new \Exception('Invalid state');
        }

        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($request->request->get('id_token'));

        $data = $token->claims()->all();

        $user = MemberModel::findByEmail($data['email']);
        $element = ContentModel::findOneById($request->cookies->get('bifroest_login_content_element'));
        $jumpTo = PageModel::findWithDetails($element->bifroest_jumpTo);

        if ($user && $user->bifroest_apple_sub === $data['sub']) {
            $userManager->loginUser($user);

            return $this->redirectAfterLogin($request, $jumpTo);
        }
        if ($user) {
            $page = PageModel::findByPk($element->bifroest_jumpTo_emailInUse);

            return $this->redirect($contentUrlGenerator->generate($page));
        }

        $module = ModuleModel::findByPk($element->module);

        $user = $userManager->createNewUser([
            'email' => $data['email'],
            'username' => $data['email'],
            'firstname' => $data['given_name'] ?? '',
            'lastname' => $data['family_name'] ?? '',
            'language' => $jumpTo->rootLanguage,
            'bifroest_apple_sub' => $data['sub'],
        ], $module);

        $userManager->loginUser($user);

        return $this->redirectAfterLogin($request, $jumpTo);
    }
}
