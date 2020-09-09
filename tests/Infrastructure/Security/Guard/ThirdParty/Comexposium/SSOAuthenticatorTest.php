<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Security\Guard\ThirdParty\Comexposium;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLoginHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOCheckerHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSORedirectionAfterLoginResolver;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\Comexposium\SSOAuthenticationSuccessHandler;
use Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\Comexposium\SSOAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SSOAuthenticatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $ssoCheckerHandler;

    /** @var ObjectProphecy */
    private $eventByHostResolver;

    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $ssoAuthenticationSuccessHandler;

    /** @var ObjectProphecy */
    private $ssoRedirectionAfterLoginResolver;

    /** @var ObjectProphecy */
    private $updateLastLoginHandler;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $token;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $redirectResponse;

    /** @var SSOAuthenticator */
    private $ssoAuthenticator;

    public function setUp(): void
    {
        $this->redirectResponse = $this->prophesize(RedirectResponse::class);
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);

        $this->request = $this->prophesize(Request::class);
        $this->request->getHost()->willReturn('www.host.tld');
        $this->request->getLocale()->willReturn('fr');

        $this->token = $this->prophesize(TokenInterface::class);
        $this->token->getUser()->willReturn($this->user->reveal());

        $this->ssoCheckerHandler = $this->prophesize(SSOCheckerHandler::class);
        $this->eventByHostResolver = $this->prophesize(EventByHostResolver::class);
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->ssoAuthenticationSuccessHandler = $this->prophesize(SSOAuthenticationSuccessHandler::class);
        $this->ssoRedirectionAfterLoginResolver = $this->prophesize(SSORedirectionAfterLoginResolver::class);
        $this->updateLastLoginHandler = $this->prophesize(UpdateLastLoginHandler::class);

        $this->ssoAuthenticator = new SSOAuthenticator(
            $this->ssoCheckerHandler->reveal(),
            $this->eventByHostResolver->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->ssoAuthenticationSuccessHandler->reveal(),
            $this->ssoRedirectionAfterLoginResolver->reveal(),
            $this->updateLastLoginHandler->reveal()
        );
    }

    public function testOnAuthenticationSuccessWithNoRedirection()
    {
        $this
            ->eventByHostResolver
            ->resolveEventFromHostAndLocale('www.host.tld', 'fr')
            ->shouldBeCalled()
            ->willReturn($this->event->reveal())
        ;

        $this
            ->ssoRedirectionAfterLoginResolver
            ->handle($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->updateLastLoginHandler
            ->handle(new UpdateLastLogin($this->event->reveal(), $this->user->reveal()))
            ->shouldBeCalled()
        ;

        $this
            ->ssoAuthenticationSuccessHandler
            ->onAuthenticationSuccess($this->request->reveal(), $this->token->reveal())
            ->shouldBeCalled()
            ->willReturn($this->redirectResponse->reveal())
        ;

        $result = $this->ssoAuthenticator->onAuthenticationSuccess(
            $this->request->reveal(),
            $this->token->reveal(),
            'whatever-provider-key'
        );

        $this->assertEquals($this->redirectResponse->reveal(), $result);
    }

    public function testOnAuthenticationSuccessWithRedirection()
    {
        $this
            ->eventByHostResolver
            ->resolveEventFromHostAndLocale('www.host.tld', 'fr')
            ->shouldBeCalled()
            ->willReturn($this->event->reveal())
        ;

        $this
            ->ssoRedirectionAfterLoginResolver
            ->handle($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn('/url')
        ;

        $this
            ->updateLastLoginHandler
            ->handle(new UpdateLastLogin($this->event->reveal(), $this->user->reveal()))
            ->shouldNotBeCalled()
        ;

        $this
            ->ssoAuthenticationSuccessHandler
            ->onAuthenticationSuccess($this->request->reveal(), $this->token->reveal())
            ->shouldNotBeCalled()
        ;

        $result = $this->ssoAuthenticator->onAuthenticationSuccess(
            $this->request->reveal(),
            $this->token->reveal(),
            'whatever-provider-key'
        );

        $this->assertEquals(new RedirectResponse('/url'), $result);
    }
}
