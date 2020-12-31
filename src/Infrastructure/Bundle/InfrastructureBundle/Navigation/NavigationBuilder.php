<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class NavigationBuilder implements NavigationBuilderInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * NavigationBuilder constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    public function getRoute($path, $parameters = [])
    {
        return $this->router->generate($path, $parameters);
    }
}
