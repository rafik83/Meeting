<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Order\UpdateRow;
use Proximum\Vimeet\Application\Command\Order\UpdateRowHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateRowHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $product = Product::createOption($event, 'Option A', 'a.jpg', 100, 20, 2, 4, 3, false);

        $rowRepository = $this->prophesize(RowRepositoryInterface::class);
        $order         = $this->prophesize(Order::class);

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

        $rowRepository->set($row)->shouldBeCalled();

        $updateRow = new UpdateRow($row);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        // Handler
        $handler = new UpdateRowHandler($rowRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($updateRow);
    }
}
