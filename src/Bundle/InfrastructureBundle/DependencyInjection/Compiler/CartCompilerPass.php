<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CartCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $carts = $container->findTaggedServiceIds('cart');
        $service = $container->getDefinition('vimeet_infrastructure.application.components.cart.cart_builder');

        foreach ($carts as $id => $tags) {
            foreach ($tags as $tag) {
                $service->addMethodCall('registerCart', [$tag['cart'], new Reference($id)]);
            }
        }
    }
}
