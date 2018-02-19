<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\ThirdParty\Comexposium\SSO;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOChecker;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\ComboEmailUserNotValidException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\Comexposium\SSO\LoginAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class LoginActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $authenticationManager;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->authenticationManager = $this->prophesize(AuthenticationManagerInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testInvoke()
    {
        $request = new Request([], ['email' => 'email@example.net', 'token' => 'token']);
        $user = $this->prophesize(User::class);

        $this->authorizationCheckerAdapter->isGranted('AUTHENTICATED_REMEMBERED')->shouldBeCalled()->willReturn(true);
        $this->authenticationManager->disconnect()->shouldBeCalled();
        $this->queryBus
            ->handle(new SSOChecker($this->event->reveal(), 'email@example.net', 'token'))
            ->shouldBeCalled()
            ->willReturn($user->reveal())
        ;
        $this->authenticationManager->authenticate($user->reveal(), 'main')->shouldBeCalled();

        $action = new LoginAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->authenticationManager->reveal(),
            $this->queryBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($request, new EventDomain($this->event->reveal()));

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(json_encode(['isLogged' => true]), $result->getContent());
    }

    public function testInvokeError()
    {
        $request = new Request([], ['email' => 'email@example.net', 'token' => 'token']);

        $this->authorizationCheckerAdapter->isGranted('AUTHENTICATED_REMEMBERED')->shouldBeCalled()->willReturn(false);
        $this->authenticationManager->disconnect()->shouldNotBeCalled();
        $this->queryBus
            ->handle(new SSOChecker($this->event->reveal(), 'email@example.net', 'token'))
            ->shouldBeCalled()
            ->willThrow(ComboEmailUserNotValidException::class)
        ;
        $this->flashBag->add('error', 'flash.sso.comexposium.error')->shouldBeCalled();

        $action = new LoginAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->authenticationManager->reveal(),
            $this->queryBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($request, new EventDomain($this->event->reveal()));

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(json_encode(['isLogged' => false]), $result->getContent());
    }
}
