<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event\RemoveAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $tip;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->tip = $this->prophesize(Tip::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
    }

    public function testAccessDeniedRole()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $action = new RemoveAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );
        $action($this->event->reveal(), $this->tip->reveal());
    }

    public function testAccessDeniedPermissionEvent()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $action = new RemoveAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );
        $action($this->event->reveal(), $this->tip->reveal());
    }

    public function testAccessDeniedEventTip()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $event = $this->prophesize(Event::class);
        $this->tip->getEvent()->willReturn($event->reveal());

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $action = new RemoveAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );
        $action($this->event->reveal(), $this->tip->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->tip->getEvent()->willReturn($this->event->reveal());
        $this->event->getId()->willReturn(12);

        $remove = new Remove($this->tip->reveal());
        $this->commandBus->handle($remove)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.remove.success')->shouldBeCalled();
        $this->router
            ->generate('admin_tip_event_list', [
                'event' => 12,
            ])
            ->shouldBeCalled()
            ->willReturn('/route')
        ;

        $action = new RemoveAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );
        $result = $action($this->event->reveal(), $this->tip->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
