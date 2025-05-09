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
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: self::TYPE, category: 'login')]
class BifroestLoginController extends AbstractContentElementController
{
    public const string TYPE = 'bifroest_login';

    public function __construct(protected array $bifroestConfig)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if (!$request->getSession()->isStarted()) {
            $request->getSession()->start();
        }

        $template->google_api_key = $this->bifroestConfig['google_api_key'];
        $template->apple_client_id = $this->bifroestConfig['apple_client_id'];

        $state = bin2hex(random_bytes(16));
        System::setCookie('bifroest_login_content_element', $model->id, time() + 3600);
        System::setCookie('bifroest_login_state', $state, time() + 3600);

        $template->state = $state;

        return $template->getResponse();
    }
}
