<?php

namespace Plenta\ContaoBifroestLogin\Entra;

class EntraConfigurationProvider implements EntraConfigurationProviderInterface
{
    private ?EntraConfiguration $configuration = null;

    public function __construct(
        protected array $bifroestConfig,
    ) {
    }

    public function getConfiguration(): ?EntraConfiguration
    {
        if (!$this->configuration) {
            $this->configuration = new EntraConfiguration(
                $this->bifroestConfig['entra_client_id'],
                $this->bifroestConfig['entra_api_secret'],
                $this->bifroestConfig['entra_tenant_id'],
            );
        }

        return $this->configuration;
    }
}