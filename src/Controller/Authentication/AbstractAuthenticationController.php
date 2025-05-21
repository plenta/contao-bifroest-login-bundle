<?php

namespace Plenta\ContaoBifroestLogin\Controller\Authentication;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAuthenticationController extends AbstractController
{
    public function __construct(
        protected ContentUrlGenerator $contentUrlGenerator,
    ) {
    }

    protected function redirectAfterLogin(Request $request, PageModel $jumpTo)
    {
        $redirect = base64_decode($request->cookies->get('bifroest_login_redirect'));

        if ($redirect) {
            return $this->redirect($redirect);
        }

        return $this->redirect($this->contentUrlGenerator->generate($jumpTo));
    }
}