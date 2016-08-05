<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Order\UpdateRow;
use Proximum\Vimeet\Application\Command\Order\UpdateRowHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateRowHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $product = Product::createOption($event, 'Option A', 'a.jpg', 100, 2, 4, 3, false);

        $rowRepository = $this->prophesize(RowRepositoryInterface::class);
        $order         = $this->prophesize(Order::class);

        $parentRow = new Order\Row(
            $order->reveal(),
            $product,
            1,
            5,
            "label",
            12.5
        );

        $row = Order\Row::createCustomRowToProduct(
            $order->reveal(),
            $parentRow,
            "label",
            1,
            12.5
        );

        $rowRepository->set($row)->shouldBeCalled();

        $updateRow = new UpdateRow($row);

        // Handler
        $handler = new UpdateRowHandler($rowRepository->reveal());
        $handler->handle($updateRow);
    }
}
