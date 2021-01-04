<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\TokenChecker;
use Proximum\Vimeet\Application\View\Adapter\Http\Response;

class TokenCheckerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $httpAdapter;

    /** @var string */
    private $comexposiumSSOValidatorEndpoint;

    public function setUp()
    {
        $this->httpAdapter = $this->prophesize(HttpAdapterInterface::class);
        $this->comexposiumSSOValidatorEndpoint = 'https://sso.endpoint/checker';
    }

    public function testIsMailTokenComboValidTrue()
    {
        $token = 't0k3n';
        $email = 'email@example.net';

        $response = new Response(200, json_encode(['result' => ['statusCode' => 0]]));

        $this->httpAdapter
            ->post(
                $this->comexposiumSSOValidatorEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                json_encode(['email' => $email], true)
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $tokenChecker = new TokenChecker($this->httpAdapter->reveal(), $this->comexposiumSSOValidatorEndpoint);
        $result = $tokenChecker->isMailTokenComboValid($email, $token);

        $this->assertTrue($result);
    }

    public function testIsMailTokenComboValidFalseResponseStatusCode401()
    {
        $token = 't0k3n';
        $email = 'email@example.net';

        $response = new Response(401, json_encode([]));

        $this->httpAdapter
            ->post(
                $this->comexposiumSSOValidatorEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                json_encode(['email' => $email], true)
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $tokenChecker = new TokenChecker($this->httpAdapter->reveal(), $this->comexposiumSSOValidatorEndpoint);
        $result = $tokenChecker->isMailTokenComboValid($email, $token);

        $this->assertFalse($result);
    }

    public function testIsMailTokenComboValidFalseResponseDoesNotContainStatusCodeOfResult()
    {
        $token = 't0k3n';
        $email = 'email@example.net';

        $response = new Response(200, json_encode([]));

        $this->httpAdapter
            ->post(
                $this->comexposiumSSOValidatorEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                json_encode(['email' => $email], true)
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $tokenChecker = new TokenChecker($this->httpAdapter->reveal(), $this->comexposiumSSOValidatorEndpoint);
        $result = $tokenChecker->isMailTokenComboValid($email, $token);

        $this->assertFalse($result);
    }

    public function testIsMailTokenComboValidFalseException()
    {
        $token = 't0k3n';
        $email = 'email@example.net';

        $this->httpAdapter
            ->post(
                $this->comexposiumSSOValidatorEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                json_encode(['email' => $email], true)
            )
            ->shouldBeCalled()
            ->willThrow(ServerErrorException::class)
        ;

        $tokenChecker = new TokenChecker($this->httpAdapter->reveal(), $this->comexposiumSSOValidatorEndpoint);
        $result = $tokenChecker->isMailTokenComboValid($email, $token);

        $this->assertFalse($result);
    }
}
