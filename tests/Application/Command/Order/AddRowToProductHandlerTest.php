<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Order\AddRowToProduct;
use Proximum\Vimeet\Application\Command\Order\AddRowToProductHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AddRowToProductHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $product  = Product::createOption($event, 'Option A', 'a.jpg', 100, 20, 2, 4, 3, false);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $order           = $this->prophesize(Order::class);
        $order->getVatRate()->willReturn(20);
        $parentRow = new Order\Row(
            $order->reveal(),
            1,
            20,
            $product,
            5,
            'label',
            12.5
        );

        $row = Order\Row::createCustomRowToProduct(
            $order->reveal(),
            $parentRow,
            'label',
            1,
            12.5,
            20
        );

        $order->addCustomRow($row)->shouldBeCalled();
        $orderRepository->set($order->reveal())->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $add = new AddRowToProduct($order->reveal(), $parentRow);
        $add->price = 12.5;
        $add->label = 'label';
        $add->quantity = 1;

        // Handler
        $handler = new AddRowToProductHandler($orderRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($add);
    }
}
