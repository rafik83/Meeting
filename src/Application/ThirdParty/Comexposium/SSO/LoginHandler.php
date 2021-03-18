<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;

class LoginHandler
{
    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var string */
    private $comexposiumLoginEndpoint;

    /** @var string */
    private $comexposiumSsoUsername;

    /** @var string */
    private $comexposiumSsoPassword;

    public function __construct(
        HttpAdapterInterface $httpAdapter,
        string $comexposiumLoginEndpoint,
        string $comexposiumSsoUsername,
        string $comexposiumSsoPassword
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->comexposiumLoginEndpoint = $comexposiumLoginEndpoint;
        $this->comexposiumSsoUsername = $comexposiumSsoUsername;
        $this->comexposiumSsoPassword = $comexposiumSsoPassword;
    }

    /**
     * @return null|string
     */
    public function loginAndGetJwtToken(): ?string
    {
        try {
            $response = $this->httpAdapter->post(
                $this->comexposiumLoginEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                json_encode([
                    'username' => $this->comexposiumSsoUsername,
                    'password' => $this->comexposiumSsoPassword,
                    'expiresIn' => '12h',
                ], true)
            );

            if (200 !== $response->statusCode) {
                return null;
            }

            $body = json_decode($response->body, true);

            return $body['result']['jwt'] ?? null;
        } catch (ServerErrorException $serverErrorException) {
            return null;
        }
    }
}
