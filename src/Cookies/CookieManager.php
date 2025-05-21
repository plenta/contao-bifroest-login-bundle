<?php

namespace Plenta\ContaoBifroestLogin\Cookies;

use Symfony\Component\HttpFoundation\Cookie;

class CookieManager
{
    /** @var Cookie[] */
    protected array $addCookies = [];

    /** @var string[] */
    protected array $removeCookies = [];

    public function addCookie(Cookie $cookie): self
    {
        $this->addCookies[] = $cookie;

        return $this;
    }

    public function removeCookie(string $name): self
    {
        $this->removeCookies[] = $name;

        return $this;
    }

    public function getAddCookies(): array
    {
        return $this->addCookies;
    }

    public function getRemoveCookies(): array
    {
        return $this->removeCookies;
    }
}