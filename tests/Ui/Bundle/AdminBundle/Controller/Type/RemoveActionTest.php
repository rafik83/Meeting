<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Type;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\Remove;
use Proximum\Vimeet\Application\Exception\Type\TypeUsedBySheetException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\RemoveAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $type;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new RemoveAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );

        $action($this->event->reveal(), $this->type->reveal());
    }

    public function testNotFound()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $event = $this->prophesize(Event::class);
        $this->type->getEvent()->willReturn($event->reveal());

        $action = new RemoveAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );

        $action($this->event->reveal(), $this->type->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->type->getEvent()->willReturn($this->event->reveal());
        $this->event->getId()->willReturn(15);
        $remove = new Remove($this->type->reveal());
        $this->commandBus->handle($remove)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.type.remove.success')->shouldBeCalled();
        $this->router->generate('admin_type_list', ['event' => 15])->shouldBeCalled()->willReturn('/route');

        $action = new RemoveAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );

        $result = $action($this->event->reveal(), $this->type->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }

    public function testInvokeExceptionHandler()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->type->getEvent()->willReturn($this->event->reveal());
        $this->event->getId()->willReturn(15);
        $remove = new Remove($this->type->reveal());
        $this->commandBus->handle($remove)->shouldBeCalled()->willThrow(TypeUsedBySheetException::class);
        $this->flashBag->add('success', 'flash.admin.type.remove.success')->shouldNotBeCalled();
        $this->flashBag->add('error', 'flash.admin.type.remove.error')->shouldBeCalled();
        $this->router->generate('admin_type_list', ['event' => 15])->shouldBeCalled()->willReturn('/route');

        $action = new RemoveAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal()
        );

        $result = $action($this->event->reveal(), $this->type->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
