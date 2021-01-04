<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\LoginHandler;
use Proximum\Vimeet\Application\View\Adapter\Http\Response;

class LoginHandlerTest extends TestCase
{
    /**
     * @dataProvider resultProvider
     */
    public function testLoginAndGetJwtToken(Response $expectedResponse, ?string $expectedResult)
    {
        $comexposiumLoginEndpoint = 'https://whatever-login-endpoint';
        $comexposiumSsoUsername = 'username';
        $comexposiumSsoPassword = 'password';

        $httpAdapter = $this->prophesize(HttpAdapterInterface::class);

        $httpAdapter
            ->post(
                $comexposiumLoginEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                json_encode([
                    'username' => $comexposiumSsoUsername,
                    'password' => $comexposiumSsoPassword,
                    'expiresIn' => '12h',
                ], true)
            )
            ->shouldBeCalled()
            ->willReturn($expectedResponse)
        ;

        $loginHandler = new LoginHandler(
            $httpAdapter->reveal(),
            $comexposiumLoginEndpoint,
            $comexposiumSsoUsername,
            $comexposiumSsoPassword
        );
        $result = $loginHandler->loginAndGetJwtToken();

        $this->assertEquals($expectedResult, $result);
    }

    public function resultProvider()
    {
        return [
            [new Response(200, '{"result": {"jwt": "whateverJwtToken"}}'), 'whateverJwtToken'],
            [new Response(200, '{"result": {}'), null],
            [new Response(403, '{}'), null],
        ];
    }
}
