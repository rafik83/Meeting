<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;

class TokenChecker
{
    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var string */
    private $comexposiumSSOValidatorEndpoint;

    /**
     * @param HttpAdapterInterface $httpAdapter
     * @param string               $comexposiumSSOValidatorEndpoint
     */
    public function __construct(
        HttpAdapterInterface $httpAdapter,
        string $comexposiumSSOValidatorEndpoint
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->comexposiumSSOValidatorEndpoint = $comexposiumSSOValidatorEndpoint;
    }

    /**
     * @param string $mail
     * @param string $token
     *
     * @return bool
     */
    public function isMailTokenComboValid(string $mail, string $token): bool
    {
        try {
            $response = $this->httpAdapter->post(
                $this->comexposiumSSOValidatorEndpoint,
                [
                    'accept'        => 'application/json',
                    'content-type'  => 'application/json',
                    'Authorization' => sprintf('Bearer %s', $token),
                ],
                json_encode(['email' => $mail])
            );

            if (200 !== $response->statusCode) {
                return false;
            }

            $body = json_decode($response->body, true);

            return isset($body['result']['statusCode']) && $body['result']['statusCode'] === 0;
        } catch (ServerErrorException $serverErrorException) {
            return false;
        }
    }
}
