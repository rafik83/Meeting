<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Order\CancelAll;
use Proximum\Vimeet\Application\Command\Order\CancelAllHandler;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackage;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackageHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Order\OrdersCancelledEvent;
use Proximum\Vimeet\Domain\Exception\Order\OrderCanNotBeCancelledException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

class CancelAllHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $delayedEventDispatcher, $cancelPackageHandler, $sheet, $order, $admin;

    public function setUp()
    {
        $this->cancelPackageHandler = $this->prophesize(CancelPackageHandler::class);
        $this->delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->order = $this->prophesize(Order::class);
        $this->admin = $this->prophesize(Admin::class);
    }

    public function testHandleNoOrder(): void
    {
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([]);

        $this->delayedEventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->cancelPackageHandler->handle(Argument::any())->shouldNotBeCalled();

        $handler = new CancelAllHandler(
            $this->cancelPackageHandler->reveal(),
            $this->delayedEventDispatcher->reveal()
        );

        $handler->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()));
    }

    public function testHandleWithInvoice(): void
    {
        $this->expectException(OrderCanNotBeCancelledException::class);

        $this->order->hasInvoice()->shouldBeCalled()->willReturn(true);
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$this->order->reveal()]);

        $this->delayedEventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->cancelPackageHandler->handle(Argument::any())->shouldNotBeCalled();

        $handler = new CancelAllHandler(
            $this->cancelPackageHandler->reveal(),
            $this->delayedEventDispatcher->reveal()
        );

        $handler->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()));
    }

    public function testHandle(): void
    {
        $this->order->hasInvoice()->shouldBeCalled()->willReturn(false);
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$this->order->reveal()]);

        $this->cancelPackageHandler->handle(new CancelPackage($this->sheet->reveal()))->shouldBeCalled();
        $this->delayedEventDispatcher
            ->dispatch(Events::SHEET_ORDERS_CANCELLED, new OrdersCancelledEvent($this->sheet->reveal(), $this->admin->reveal()))
            ->shouldBeCalled()
        ;

        $handler = new CancelAllHandler(
            $this->cancelPackageHandler->reveal(),
            $this->delayedEventDispatcher->reveal()
        );

        $handler->handle(new CancelAll($this->sheet->reveal(), $this->admin->reveal()));
    }
}
