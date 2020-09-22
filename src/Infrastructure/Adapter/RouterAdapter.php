<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface as SymfonyRouterInterface;

class RouterAdapter implements RouterInterface
{
    /** @var SymfonyRouterInterface */
    private $router;

    /** @var string */
    private $scheme;

    public function __construct(SymfonyRouterInterface $router, string $scheme)
    {
        $this->router = $router;
        $this->scheme = $scheme;
    }

    public function generate($path, array $parameters = []): string
    {
        return $this->router->generate($path, $parameters);
    }

    public function url($path, array $parameters = []): string
    {
        $this->router->getContext()->setScheme($this->scheme);
        return $this->router->generate($path, $parameters, SymfonyRouterInterface::ABSOLUTE_URL);
    }

    /**
     * @return RequestContext
     */
    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }
}
