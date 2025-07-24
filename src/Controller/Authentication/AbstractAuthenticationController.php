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

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAuthenticationController extends AbstractController
{
    public function __construct(
        protected ContentUrlGenerator $contentUrlGenerator,
    ) {
    }

    protected function redirectAfterLogin(Request $request, PageModel $jumpTo)
    {
        $redirect = base64_decode($request->cookies->get('bifroest_login_redirect'), true);

        if ($redirect) {
            return $this->redirect($redirect);
        }

        return $this->redirect($this->contentUrlGenerator->generate($jumpTo));
    }
}
