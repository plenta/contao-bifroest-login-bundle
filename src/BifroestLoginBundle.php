<?php

declare(strict_types=1);

/**
 * Bifröst Login Bundle for Contao Open Source CMS
 *
 * @copyright     Copyright (c) 2025, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoBifroestLogin;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class BifroestLoginBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('linkedin_api_key')->defaultValue('')->end()
                ->scalarNode('linkedin_api_secret')->defaultValue('')->end()
                ->scalarNode('google_api_key')->defaultValue('')->end()
                ->scalarNode('apple_client_id')->defaultValue('')->end()
                ->scalarNode('entra_api_key')->defaultValue('')->end()
                ->scalarNode('entra_api_secret')->defaultValue('')->end()
                ->scalarNode('entra_tenant_id')->defaultValue('')->end()
                ->scalarNode('entra_client_id')->defaultValue('')->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yml');
        $container->parameters()->set('bifroest_linkedin_api_key', $config['linkedin_api_key']);
        $container->parameters()->set('bifroest_linkedin_api_secret', $config['linkedin_api_secret']);
        $container->parameters()->set('bifroest_google_api_key', $config['google_api_key']);
        $container->parameters()->set('bifroest_apple_client_id', $config['apple_client_id']);
        $container->parameters()->set('bifroest_entra_api_key', $config['entra_api_key']);
        $container->parameters()->set('bifroest_entra_api_secret', $config['entra_api_secret']);
        $container->parameters()->set('bifroest_entra_tenant_id', $config['entra_tenant_id']);
        $container->parameters()->set('bifroest_entra_client_id', $config['entra_client_id']);
    }
}
