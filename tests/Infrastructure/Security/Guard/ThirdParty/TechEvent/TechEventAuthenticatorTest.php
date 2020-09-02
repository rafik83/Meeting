<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Security\Guard\ThirdParty\TechEvent;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLoginHandler;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\TechEvent\TechEventAuthenticationSuccessHandler;
use Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\TechEvent\TechEventAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class TechEventAuthenticatorTest extends TestCase
{
    private $redirectResponse,
        $event,
        $user,
        $token,
        $eventByHostResolver,
        $flashBag,
        $router,
        $authenticationSuccessHandler,
        $updateLastLoginHandler,
        $userEventExtraDataRepository,
        $userRepository,
        $techEventAuthenticatorSuccessHandler,
        $csrfTokenManager
    ;

    /** @var TechEventAuthenticator */
    private $techEventAuthenticator;

    /** @var Request */
    private $request;

    public function setUp(): void
    {
        $this->redirectResponse = $this->prophesize(RedirectResponse::class);
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);

        $this->request = Request::create(
            'http://www.host.tld/fr/login',
            'POST',
            [
                'login' => [
                    'username' => 'anne@sophie.fr',
                    'password' => 'AnneSophie75',
                    '_token' => '1234567890'
                ]
            ]
        );
        $this->request->setLocale('fr');
        $this->request->attributes->add([
            '_route' => Route::TECH_EVENT_LOGIN_CHECK,
        ]);

        $this->token = $this->prophesize(TokenInterface::class);
        $this->token->getUser()->willReturn($this->user->reveal());

        $this->eventByHostResolver = $this->prophesize(EventByHostResolver::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->authenticationSuccessHandler = $this->prophesize(TechEventAuthenticationSuccessHandler::class);
        $this->updateLastLoginHandler = $this->prophesize(UpdateLastLoginHandler::class);
        $this->userEventExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->techEventAuthenticatorSuccessHandler = $this->prophesize(TechEventAuthenticationSuccessHandler::class);
        $this->csrfTokenManager = $this->prophesize(CsrfTokenManagerInterface::class);

        $this->techEventAuthenticator = new TechEventAuthenticator(
            $this->eventByHostResolver->reveal(),
            $this->userEventExtraDataRepository->reveal(),
            $this->userRepository->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->updateLastLoginHandler->reveal(),
            $this->techEventAuthenticatorSuccessHandler->reveal(),
            $this->csrfTokenManager->reveal()
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        $this
            ->eventByHostResolver
            ->resolveEventFromHostAndLocale('www.host.tld', 'fr')
            ->shouldBeCalled()
            ->willReturn($this->event)
        ;

        $this
            ->updateLastLoginHandler
            ->handle(new UpdateLastLogin($this->event->reveal(), $this->user->reveal()))
            ->shouldBeCalled()
        ;

        $this
            ->techEventAuthenticatorSuccessHandler
            ->onAuthenticationSuccess($this->request, $this->token->reveal())
            ->shouldBeCalled()
            ->willReturn($this->redirectResponse->reveal())
        ;

        $token = new CsrfToken(
            'authenticate',
            '1234567890'
        );
        $this->csrfTokenManager
            ->isTokenValid($token)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->userRepository
            ->findByEmail('anne@sophie.fr')
            ->shouldBeCalled()
            ->willReturn($this->user->reveal())
        ;

        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $extraData->getValue()
            ->shouldBeCalled()
            ->willReturn('1b6bab60821576de0a51a36f157aff47ce0bfbb2')
        ;
        $this->userEventExtraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::TECH_EVENT_LOGIN_DATA,
                $this->user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData)
        ;

        $this->assertTrue($this->techEventAuthenticator->supports($this->request));

        $credentials = $this->techEventAuthenticator->getCredentials($this->request);

        $expectedCredentials = [
            'email' => 'anne@sophie.fr',
            'password' => 'AnneSophie75',
            'locale' => 'fr',
            'csrf_token' => '1234567890',
        ];

        $this->assertEquals($expectedCredentials['email'], $credentials['email']);
        $this->assertEquals($expectedCredentials['password'], $credentials['password']);
        $this->assertEquals($expectedCredentials['locale'], $credentials['locale']);
        $this->assertEquals($expectedCredentials['csrf_token'], $credentials['csrf_token']);

        $userProvider = $this->prophesize(UserProviderInterface::class);
        $givenUser = $this->techEventAuthenticator->getUser($credentials, $userProvider->reveal());

        $this->assertInstanceOf(User::class, $givenUser);

        $this->assertTrue($this->techEventAuthenticator
            ->checkCredentials($credentials, $this->user->reveal()));


        $result = $this->techEventAuthenticator->onAuthenticationSuccess(
            $this->request,
            $this->token->reveal(),
            'whatever-provider-key'
        );

        $this->assertEquals($this->redirectResponse->reveal(), $result);
    }
}
