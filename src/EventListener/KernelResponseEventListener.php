<?php

namespace Plenta\ContaoBifroestLogin\EventListener;

use Plenta\ContaoBifroestLogin\Cookies\CookieManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class KernelResponseEventListener
{
    public function __construct(
        protected CookieManager $cookieManager,
    ) {
    }

    #[AsEventListener(event: 'kernel.response')]
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        if (!empty($this->cookieManager->getAddCookies())) {
            foreach ($this->cookieManager->getAddCookies() as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        if (!empty($this->cookieManager->getRemoveCookies())) {
            foreach ($this->cookieManager->getRemoveCookies() as $cookie) {
                $response->headers->clearCookie($cookie);
            }
        }

        $event->setResponse($response);
    }
}