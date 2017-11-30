<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip\Event;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Application\Command\Tip\Event\RemoveHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\UnAssignedEvent;
use Proximum\Vimeet\Application\Exception\Tip\TipNotAffectedOnEventException;
use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\TipFactory;
use PHPUnit\Framework\TestCase;

class RemoveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $tip   = TipFactory::createTip('Awsm tip');
        $tip->setType($type);

        $tipRepository->getByEventAndTip($event, $tip)->shouldBeCalled()->willReturn($tip);
        $tipRepository->removeTip($tip)->shouldBeCalled();
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::TIP_UN_ASSIGNED, new UnAssignedEvent($event, $tip))->shouldBeCalled();

        $remove  = new Remove($event, $tip);
        $handler = new RemoveHandler(
            $tipRepository->reveal(),
            $eventDispatcher->reveal()
        );


        $handler->handle($remove);

    }

    public function testRemoveWithWrongTip()
    {
        $this->expectException(TipNotAffectedOnEventException::class);
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $event = EventFactory::createEvent();
        $event2 = EventFactory::createEvent();
        $type2 = new Type($event2);
        $tip   = TipFactory::createTip('Awsm tip');
        $tip->setType($type2);

        $tipRepository->getByEventAndTip($event, $tip)->shouldBeCalled()->willReturn($tip);
        $tipRepository->removeTip($tip)->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $remove  = new Remove($event, $tip);
        $handler = new RemoveHandler($tipRepository->reveal(), $eventDispatcher->reveal());

        $handler->handle($remove);
    }

    public function testTipNotFoundException()
    {
        $this->expectException(TipNotFoundException::class);
        $event   = EventFactory::createEvent();
        $tip     = TipFactory::createTip('Awsm tip');

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tipRepository->getByEventAndTip($event, $tip)
            ->shouldBeCalled()
            ->willReturn(null);
        $tipRepository->removeTip($tip)->shouldNotBeCalled();
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $remove  = new Remove($event, $tip);
        $handler = new RemoveHandler($tipRepository->reveal(), $eventDispatcher->reveal());

        $handler->handle($remove);
    }
}
