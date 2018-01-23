<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface as SymfonyRouterInterface;

class RouterAdapter implements RouterInterface
{
    /**
     * @var SymfonyRouterInterface
     */
    private $router;

    /**
     * RouterAdapter constructor.
     *
     * @param SymfonyRouterInterface $router
     */
    public function __construct(SymfonyRouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($path, array $parameters = [])
    {
        return $this->router->generate($path, $parameters);
    }

    /**
     * @return RequestContext
     */
    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }
}
