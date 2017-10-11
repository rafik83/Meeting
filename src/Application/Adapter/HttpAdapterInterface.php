<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\View\Adapter\Http\Response;

interface HttpAdapterInterface
{
    /**
     * @param string $uri
     * @param array  $headers
     * @param        $body
     *
     * @throws ServerErrorException
     *
     * @return Response
     */
    public function post($uri, array $headers, $body): Response;
}
