<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
