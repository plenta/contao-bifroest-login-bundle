<?php

namespace Plenta\ContaoBifroestLogin\Entra;

class EntraConfiguration
{
    public function __construct(
        protected string $clientId,
        protected string $apiSecret,
        protected string $tenantId,
    ) {}

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }
}