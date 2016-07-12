<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation;

use Proximum\Vimeet\Domain\Navigation\NavigationInterface;
use Symfony\Component\Routing\Router;

class NavigationBuilder implements NavigationBuilderInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * NavigationBuilder constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $path
     * @param array  $parameter
     *
     * @return string
     */
    public function getRoute($path, $parameter = [])
    {
        return $this->router->generate($path, $parameter);
    }
}
