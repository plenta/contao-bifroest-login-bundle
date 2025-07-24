<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

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
