<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
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
        $client = new Client();
        $request = new Request('POST', $uri, $headers, $body);

        try {
            $response = $client->send($request);

            return new Response($response->getStatusCode(), (string) $response->getBody());
        } catch (ServerException $exception) {
            throw new ServerErrorException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (ClientException $clientErrorResponseException) {
            return new Response(
                $clientErrorResponseException->getResponse()->getStatusCode(),
                (string) $clientErrorResponseException->getResponse()->getBody()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $uri, array $headers = [], array $options = []): Response
    {
        $client = new Client();
        $request = new Request(
            'GET',
            $uri,
            $headers
        );

        try {
            $guzzleResponse = $client->send($request, $options);

            return new Response($guzzleResponse->getStatusCode(), (string) $guzzleResponse->getBody());
        } catch (ServerException $exception) {
            throw new ServerErrorException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (ClientException $clientErrorResponseException) {
            return new Response(
                $clientErrorResponseException->getResponse()->getStatusCode(),
                (string) $clientErrorResponseException->getResponse()->getBody()
            );
        }
    }
}
