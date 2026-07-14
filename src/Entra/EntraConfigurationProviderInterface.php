<?php

namespace Plenta\ContaoBifroestLogin\Entra;

interface EntraConfigurationProviderInterface
{
    public function getConfiguration(): ?EntraConfiguration;
}