<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Order\Create;
use Proximum\Vimeet\Application\Command\Order\CreateHandler;
use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $orderRepository    = $this->prophesize(OrderRepositoryInterface::class);
        $sheetRepository    = $this->prophesize(SheetRepositoryInterface::class);
        $cartRepository     = $this->prophesize(CartRepositoryInterface::class);
        $orderManager       = $this->prophesize(OrderManager::class);
        $participantManager = $this->prophesize(ParticipantManager::class);

        $event     = new Event();
        $type      = new Type($event);
        $sheet     = new Sheet($event, $type, [], []);
        $createdAt = new \DateTime();
        $cart      = new Cart([], [], $sheet, $createdAt);

        $expectedOrder = new Order($sheet, Order::STATE_UNPAID, [], [], [], [], $createdAt, 'card');

        $create = new Create($cart, $sheet, [], [], [], [], $createdAt);
        $create->paymentMode = 'card';

        $orderManager->cleanFalseOption([])->shouldBeCalled()->willReturn([]);
        $orderRepository->add($expectedOrder)->shouldBeCalled();
        $participantManager->convertInactiveParticipantAfterOrderCreation($sheet, $expectedOrder)->shouldBeCalled();
        $orderManager->mergeTwoPackageData([], [])->shouldBeCalled()->willReturn([]);

        $cartRepository->delete($cart)->shouldBeCalled();
        $sheetRepository->set($sheet)->shouldBeCalled();

        $handler = new CreateHandler(
            $orderRepository->reveal(),
            $sheetRepository->reveal(),
            $cartRepository->reveal(),
            $orderManager->reveal(),
            $participantManager->reveal()
        );
        $handler->handle($create);
    }
}
