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
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;
use Plenta\ContaoBifroestLogin\UserManager\Manager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('_bifroest/auth/entra', defaults: ['_scope' => 'frontend'])]
class EntraController extends AbstractAuthenticationController
{
    protected const BASE_URL = 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0';

    public function __construct(protected array $bifroestConfig, protected ContentUrlGenerator $contentUrlGenerator, protected HttpClientInterface $client)
    {
        parent::__construct($contentUrlGenerator);
    }

    #[Route('/register', name: 'bifroest_entra_register')]
    public function register(Request $request, array $bifroestConfig, RouterInterface $router)
    {
        return $this->redirect($this->replaceTenant(self::BASE_URL).'/authorize?response_type=code&client_id='.$bifroestConfig['entra_client_id'].'&redirect_uri='.$router->generate('bifroest_entra_callback', referenceType: UrlGeneratorInterface::ABSOLUTE_URL).'&state='.$request->cookies->get('bifroest_login_state').'&scope=openid%20email%20profile&nonce='.$request->cookies->get('bifroest_login_nonce').'&response_mode=query');
    }

    #[Route('/callback', name: 'bifroest_entra_callback')]
    public function callback(
        Request $request,
        HttpClientInterface $client,
        array $bifroestConfig,
        RouterInterface $router,
        Manager $userManager,
        ContentUrlGenerator $contentUrlGenerator,
    ) {
        $code = $request->query->get('code');
        $state = $request->query->get('state');

        if ($state !== $request->cookies->get('bifroest_login_state')) {
            throw new \Exception('Invalid state');
        }

        $response = $client->request('POST', $this->replaceTenant(self::BASE_URL).'/token', [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $bifroestConfig['entra_client_id'],
                'client_secret' => $bifroestConfig['entra_api_secret'],
                'redirect_uri' => $router->generate('bifroest_entra_callback', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            ],
        ]);

        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($response->toArray()['id_token']);

        $key = $this->getKey($token->headers()->get('kid'));

        $constraints = [
            new SignedWith(new Sha256(), $key),
            new IssuedBy($this->replaceTenant('https://login.microsoftonline.com/{tenant}/v2.0')),
            new PermittedFor($this->bifroestConfig['entra_client_id']),
            new StrictValidAt(SystemClock::fromUTC()),
        ];

        $validator = new Validator();

        if (!$validator->validate($token, ...$constraints)) {
            throw new \Exception('Invalid token');
        }

        $data = $token->claims()->all();

        if ($data['nonce'] !== $request->cookies->get('bifroest_login_nonce')) {
            throw new \Exception('Invalid nonce');
        }

        $user = MemberModel::findByEmail($data['preferred_username'] ?: $data['email']);
        $element = ContentModel::findOneById($request->cookies->get('bifroest_login_content_element'));
        $jumpTo = PageModel::findByPk($element->bifroest_jumpTo);

        if ($user && (($user->bifroest_entra_oid === $data['oid'] && $user->bifroest_entra_tid === $data['tid']) || $element->bifroest_auto_connect)) {
            if ($user->bifroest_entra_oid !== $data['oid'] || $user->bifroest_entra_tid !== $data['tid']) {
                $user->bifroest_entra_oid = $data['oid'];
                $user->bifroest_entra_tid = $data['tid'];
                $user->save();
            }

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
            'firstname' => $data['name'] ?? '',
            'bifroest_entra_oid' => $data['oid'],
            'bifroest_entra_tid' => $data['tid'],
        ], $module);

        $userManager->loginUser($user);

        return $this->redirectAfterLogin($request, $jumpTo);
    }

    protected function replaceTenant(string $url)
    {
        return str_replace('{tenant}', $this->bifroestConfig['entra_tenant_id'], $url);
    }

    protected function getKey(string $kid): InMemory
    {
        $data = $this->client->request('GET', $this->replaceTenant('https://login.microsoftonline.com/{tenant}/v2.0/.well-known/openid-configuration'));

        $jwks = $this->client->request('GET', $data->toArray()['jwks_uri']);

        foreach ($jwks->toArray()['keys'] as $key) {
            if ($key['kid'] === $kid) {
                $pemCertificate = sprintf(
                    "-----BEGIN CERTIFICATE-----\n%s-----END CERTIFICATE-----\n",
                    chunk_split($key['x5c'][0], 64, "\n")
                );

                $resource = openssl_pkey_get_public($pemCertificate);

                if ($resource === false) {
                    throw new \RuntimeException('Unable to extract public key from certificate.');
                }

                $details = openssl_pkey_get_details($resource);

                if ($details === false || !isset($details['key'])) {
                    throw new \RuntimeException('Unable to obtain public key.');
                }

                return InMemory::plainText($details['key']);
            }
        }

        throw new \Exception('Key not found');
    }
}
