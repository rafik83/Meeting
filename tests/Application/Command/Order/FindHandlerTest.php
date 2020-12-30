<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Order\Find;
use Proximum\Vimeet\Application\Command\Order\FindHandler;
use Proximum\Vimeet\Application\Command\Order\FindResult;
use Proximum\Vimeet\Application\Exception\Order\InvalidNumeroOrderException;
use Proximum\Vimeet\Application\Exception\Order\IsNotAllowedToFindOrderException;
use Proximum\Vimeet\Application\Exception\Order\OrderNotFoundException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Numero\OrderNumeroView;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class FindHandlerTest extends TestCase
{
    public function testHandleNotAllowedToFind()
    {
        $numero = '1-12-34';
        $this->expectException(IsNotAllowedToFindOrderException::class);

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(true);
        $admin->getId()->willReturn(1);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $handler = new FindHandler($orderRepository->reveal());
        $handler->handle(new Find($admin->reveal(), $numero));
    }

    public function testHandleNotValidNumero()
    {
        $numero = '12-34';
        $this->expectException(InvalidNumeroOrderException::class);

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $handler = new FindHandler($orderRepository->reveal());
        $handler->handle(new Find($admin->reveal(), $numero));
    }

    public function testHandleOrderNotFound()
    {
        $this->expectException(OrderNotFoundException::class);

        $numero = '1-12-34';

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);

        $numeroView = new OrderNumeroView(1, 12, 34);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findByNumero($numeroView)->shouldBeCalled()->willReturn(null);

        $handler = new FindHandler($orderRepository->reveal());
        $handler->handle(new Find($admin->reveal(), $numero));
    }

    public function testHandle()
    {
        $numero = '1-12-34';

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);
        $admin->hasAccessToAllEvent()->willReturn(true);

        $order = $this->prophesize(Order::class);
        $sheet = $this->prophesize(Sheet::class);
        $order->getSheet()->willReturn($sheet->reveal());

        $numeroView = new OrderNumeroView(1, 12, 34);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findByNumero($numeroView)->shouldBeCalled()->willReturn($order);

        $handler = new FindHandler($orderRepository->reveal());
        $result = $handler->handle(new Find($admin->reveal(), $numero));

        $expected = new FindResult($sheet->reveal());

        $this->assertEquals($expected, $result);
    }
}
