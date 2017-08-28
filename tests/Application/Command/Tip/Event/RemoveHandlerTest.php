<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Application\Command\Tip\Event\RemoveHandler;
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

        $remove  = new Remove($event, $tip);
        $handler = new RemoveHandler($tipRepository->reveal());

        $tipRepository->getByEventAndTip($remove->event, $remove->tip)
            ->shouldBeCalled()
            ->willReturn($tip);

        $tipRepository->removeTip($tip)->shouldBeCalled();

        $handler->handle($remove);

    }

    public function testRemoveWithWrongTip()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $event = EventFactory::createEvent();
        $event2 = EventFactory::createEvent();
        $type2 = new Type($event2);
        $tip   = TipFactory::createTip('Awsm tip');

        $tip->setType($type2);

        $remove  = new Remove($event, $tip);
        $handler = new RemoveHandler($tipRepository->reveal());

        $tipRepository->getByEventAndTip($remove->event, $remove->tip)
            ->shouldBeCalled()
            ->willReturn($tip);

        $this->expectException(TipNotAffectedOnEventException::class);

        $tipRepository->removeTip($tip)->shouldNotBeCalled();

        $handler->handle($remove);
    }

    public function testTipNotFoundException()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $event   = EventFactory::createEvent();
        $tip     = TipFactory::createTip('Awsm tip');
        $remove  = new Remove($event, $tip);
        $handler = new RemoveHandler($tipRepository->reveal());

        $tipRepository->getByEventAndTip($remove->event, $remove->tip)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->expectException(TipNotFoundException::class);

        $tipRepository->removeTip($tip)->shouldNotBeCalled();

        $handler->handle($remove);
    }
}
