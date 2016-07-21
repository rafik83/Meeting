<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
