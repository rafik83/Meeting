<?php

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
    public function post(string $uri, array $headers = [], $body): Response;

    /**
     * @param string $uri
     * @param array  $headers
     * @param array  $options
     *
     * @throws ServerErrorException
     *
     * @return Response
     */
    public function get(string $uri, array $headers = [], array $options = []): Response;
}
