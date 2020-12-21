<?php

namespace Proximum\Vimeet\Infrastructure\Tokbox;

use Firebase\JWT\JWT;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use OpenTok\Exception\BroadcastDomainException;
use Psr\Http\Message\RequestInterface;

/**
 * This Client is used to call the Opentok Rest endpoint not available in the Opentok PHP SDK
 */
class Client
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $apiSecret;

    /** @var string */
    private $apiUrl;

    /** @var GuzzleClient */
    private $guzzleClient;

    public function __construct(
        string $apiKey,
        string $apiSecret
    ) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiUrl = 'https://api.opentok.com';

        $handlerStack = HandlerStack::create();
        $handler = Middleware::mapRequest(function (RequestInterface $request) {
            $authHeader = $this->createAuthHeader();
            return $request->withHeader('X-OPENTOK-AUTH', $authHeader);
        });
        $handlerStack->push($handler);

        $this->guzzleClient = new GuzzleClient([
            'base_uri' => $this->apiUrl,
            'handler' => $handlerStack,
        ]);
    }

    public function createAuthHeader()
    {
        $token = array(
            'ist' => 'project',
            'iss' => $this->apiKey,
            'iat' => time(), // this is in seconds
            'exp' => time()+(5 * 60),
            'jti' => uniqid(),
        );

        return JWT::encode($token, $this->apiSecret);
    }

    public function getBroadcasts(): array
    {
        $request = new Request(
            'GET',
            '/v2/project/' . $this->apiKey . '/broadcast'
        );

        try {
            $response = $this->guzzleClient->send($request, [
                'debug' => false,
            ]);

            $list = json_decode($response->getBody(), true);
        } catch (\Exception $exception) {
            throw new BroadcastDomainException($exception);
        }

        return $list;
    }

    public function getBroadcastForSession(string $sessionId): array
    {
        $elements = $this->getBroadcastsForSession($sessionId);

        return $elements[0] ?? [];
    }


    public function getBroadcastsForSession(string $sessionId): array
    {
        $request = new Request(
            'GET',
            '/v2/project/' . $this->apiKey . '/broadcast?sessionId=' . $sessionId
        );

        try {
            $response = $this->guzzleClient->send($request, []);

            $broadcastJson = json_decode($response->getBody(), true);
        } catch (\Exception $exception) {
            throw new BroadcastDomainException($exception);
        }

        return $broadcastJson['items'] ?? [];
    }
}
