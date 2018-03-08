<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\View\Adapter\Http\Response;

class HttpAdapter implements HttpAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function post(string $uri, array $headers = [], $body): Response
    {
        $client = new Client($uri);
        $resource = $client->post($uri, $headers, $body);

        try {
            $guzzleResponse = $client->send($resource);

            $response = new Response($guzzleResponse->getStatusCode(), (string) $guzzleResponse->getBody());
        } catch (ServerErrorResponseException $exception) {
            throw new ServerErrorException($exception->getMessage());
        } catch (ClientErrorResponseException $clientErrorResponseException) {
            $response = new Response(
                $clientErrorResponseException->getResponse()->getStatusCode(),
                (string) $clientErrorResponseException->getResponse()->getBody()
            );
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $uri, array $headers = [], array $options = []): Response
    {
        $client = new Client($uri);
        $resource = $client->get($uri, $headers, $options);

        try {
            $guzzleResponse = $client->send($resource);
        } catch (ServerErrorResponseException $exception) {
            throw new ServerErrorException($exception->getMessage());
        }

        return new Response($guzzleResponse->getStatusCode(), (string) $guzzleResponse->getBody());
    }
}
