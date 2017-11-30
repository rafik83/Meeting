<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Symfony\Component\Routing\RequestContext;

interface RouterInterface
{
    /**
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    public function generate($path, array $parameters = []);

    /**
     * @return RequestContext
     */
    public function getContext(): RequestContext;
}
