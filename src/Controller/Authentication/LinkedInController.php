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
use Plenta\ContaoBifroestLogin\UserManager\Manager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('_bifroest/auth/linkedin', defaults: ['_scope' => 'frontend'])]
class LinkedInController extends AbstractController
{
    protected const BASE_URL = 'https://www.linkedin.com/oauth/v2';

    #[Route('/register', name: 'bifroest_linkedin_register')]
    public function register(Request $request, array $bifroestConfig, RouterInterface $router)
    {
        return $this->redirect(self::BASE_URL.'/authorization?response_type=code&client_id='.$bifroestConfig['linkedIn_api_key'].'&redirect_uri='.$router->generate('bifroest_linkedin_callback', referenceType: UrlGeneratorInterface::ABSOLUTE_URL).'&state='.$request->cookies->get('bifroest_login_state').'&scope=openid%20email%20profile');
    }

    #[Route('/callback', name: 'bifroest_linkedin_callback')]
    public function callback(
        Request $request,
        HttpClientInterface $client,
        array $bifroestConfig,
        RouterInterface $router,
        Manager $userManager,
        ContentUrlGenerator $contentUrlGenerator
    ) {
        $code = $request->query->get('code');
        $state = $request->query->get('state');

        if ($state !== $request->cookies->get('bifroest_login_state')) {
            throw new \Exception('Invalid state');
        }

        $response = $client->request('POST', self::BASE_URL.'/accessToken', [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $bifroestConfig['linkedIn_api_key'],
                'client_secret' => $bifroestConfig['linkedIn_api_secret'],
                'redirect_uri' => $router->generate('bifroest_linkedin_callback', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            ],
        ]);

        $data = $client->request('GET', 'https://api.linkedin.com/v2/userinfo', [
            'auth_bearer' => $response->toArray()['access_token'],
        ])->toArray();

        $user = MemberModel::findByEmail($data['email']);
        $element = ContentModel::findOneById($request->cookies->get('bifroest_login_content_element'));
        $jumpTo = PageModel::findByPk($element->bifroest_jumpTo);

        if ($user && $user->bifroest_linkedin_sub === $data['sub']) {
            $userManager->loginUser($user);

            return $this->redirect($contentUrlGenerator->generate($jumpTo));
        }
        if ($user) {
            $page = PageModel::findByPk($element->bifroest_jumpTo_emailInUse);

            return $this->redirect($contentUrlGenerator->generate($page));
        }

        $module = ModuleModel::findByPk($element->module);

        $user = $userManager->createNewUser([
            'email' => $data['email'],
            'username' => $data['email'],
            'firstname' => $data['given_name'],
            'lastname' => $data['family_name'],
            'country' => $data['locale']['country'],
            'language' => $data['locale']['language'],
            'bifroest_linkedin_sub' => $data['sub'],
        ], $module);

        $userManager->loginUser($user);

        return $this->redirect($contentUrlGenerator->generate($jumpTo));
    }
}
