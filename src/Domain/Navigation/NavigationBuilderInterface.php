<?php

namespace Proximum\Vimeet\Domain\Navigation;

interface NavigationBuilderInterface
{
    /**
     * @param string $path
     * @param array  $parameter
     *
     * @return string
     */
    public function getRoute($path, $parameter = []);
}
