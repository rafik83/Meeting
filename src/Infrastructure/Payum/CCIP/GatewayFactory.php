<?php

namespace Proximum\Vimeet\Infrastructure\Payum\CCIP;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as PayumGatewayFactory;

class GatewayFactory extends PayumGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'ccip',
            'payum.factory_title' => 'CCIP',
        ]);
    }
}
