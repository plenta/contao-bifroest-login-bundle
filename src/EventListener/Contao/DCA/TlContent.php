<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoBifroestLogin\EventListener\Contao\DCA;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Plenta\ContaoBifroestLogin\Controller\Contao\ContentElement\BifroestLoginController;

class TlContent
{
    public function __construct(
        #[\SensitiveParameter]
        protected array $bifroestConfig,
    ) {
    }

    #[AsCallback(table: 'tl_content', target: 'fields.bifroest_services.options')]
    public function servicesOptions()
    {
        $services = [];

        if (!empty($this->bifroestConfig['linkedIn_api_key']) && !empty($this->bifroestConfig['linkedIn_api_secret'])) {
            $services[] = 'linkedIn';
        }

        if (!empty($this->bifroestConfig['google_api_key'])) {
            $services[] = 'google';
        }

        if (!empty($this->bifroestConfig['apple_client_id'])) {
            $services[] = 'apple';
        }

        return $services;
    }

    #[AsCallback(table: 'tl_content', target: 'fields.module.attributes')]
    public function moduleAttributes(array $attributes, ?DataContainer $dc = null)
    {
        if (BifroestLoginController::TYPE === $dc?->activeRecord->type) {
            $attributes['label'] = &$GLOBALS['TL_LANG']['tl_content']['bifroest_module'][0];
            $attributes['description'] = &$GLOBALS['TL_LANG']['tl_content']['bifroest_module'][1];
        }

        dump($attributes);

        return $attributes;
    }
}
