<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoBifroestLogin\Controller\Contao\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\StringUtil;
use Contao\System;
use Nyholm\Psr7\Uri;
use Plenta\ContaoBifroestLogin\Cookies\CookieManager;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: self::TYPE, category: 'login')]
class BifroestLoginController extends AbstractContentElementController
{
    public const string TYPE = 'bifroest_login';

    public function __construct(protected array $bifroestConfig, protected CookieManager $cookieManager)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $template->google_api_key = $this->bifroestConfig['google_api_key'];
        $template->apple_client_id = $this->bifroestConfig['apple_client_id'];

        if ($model->bifroest_friendly_forwarding && $referer = $request->headers->get('referer'))
        {
            $refererUri = new Uri($referer);
            $requestUri = new Uri($request->getUri());

            // Use the HTTP referer as a fallback, but only if scheme and host matches with the current request (see #5860)
            if ($refererUri->getScheme() === $requestUri->getScheme() && $refererUri->getHost() === $requestUri->getHost() && $refererUri->getPort() === $requestUri->getPort())
            {
                $this->cookieManager->addCookie(Cookie::create('bifroest_login_redirect', StringUtil::specialchars(base64_encode((string) $refererUri)), time() + 3600)->withSameSite('None'));
            }
        }

        $state = bin2hex(random_bytes(16));
        $this->cookieManager->addCookie(Cookie::create('bifroest_login_state', $state, time() + 3600)->withSameSite('None'));
        $this->cookieManager->addCookie(Cookie::create('bifroest_login_content_element', (string) $model->id, time() + 3600)->withSameSite('None'));

        $template->state = $state;

        return $template->getResponse();
    }
}
