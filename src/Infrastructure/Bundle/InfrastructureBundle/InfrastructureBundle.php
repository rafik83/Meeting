<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\DependencyInjection\Compiler\CartCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class InfrastructureBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CartCompilerPass());
    }
}
