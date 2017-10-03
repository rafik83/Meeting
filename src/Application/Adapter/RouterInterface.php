<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface RouterInterface
{
    /**
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    public function generate($path, array $parameters = []);
}
