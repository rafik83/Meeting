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
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOChecker;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\ComboEmailUserNotValidException;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\Comexposium\SSO\LoginAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LoginActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraParameterRepository;

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
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testInvokeNotAuthorized()
    {
        $this->expectException(AccessDeniedException::class);

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $request = new Request([], ['email' => 'email@example.net', 'token' => 'token']);

        $this->authorizationCheckerAdapter->isGranted(Argument::any())->shouldNotBeCalled();
        $this->authenticationManager->disconnect()->shouldNotBeCalled();
        $this->queryBus->handle(Argument::any())->shouldNotBeCalled();
        $this->authenticationManager->authenticate(Argument::any())->shouldNotBeCalled();

        $action = new LoginAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->authenticationManager->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->queryBus->reveal(),
            $this->flashBag->reveal()
        );
        $action($request, new EventDomain($this->event->reveal()));
    }

    public function testInvoke()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;
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
            $this->extraParameterRepository->reveal(),
            $this->queryBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($request, new EventDomain($this->event->reveal()));

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(json_encode(['isLogged' => true]), $result->getContent());
    }

    public function testInvokeError()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;
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
            $this->extraParameterRepository->reveal(),
            $this->queryBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($request, new EventDomain($this->event->reveal()));

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(json_encode(['isLogged' => false]), $result->getContent());
    }
}
