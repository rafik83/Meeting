<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip\Event;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\AssignedEvent;
use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Application\View\Tip\Event\TipView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Application\Command\Tip\Event\AffectHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use PHPUnit\Framework\TestCase;

class AffectHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $tipRepository;
    
    /** @var ObjectProphecy */
    private $typeRepository;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $event;
    
    public function setUp()
    {
        $this->event           = $this->prophesize(Event::class);
        $this->tipRepository   = $this->prophesize(TipRepositoryInterface::class);
        $this->typeRepository  = $this->prophesize(TypeRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }

    public function testHandle()
    {
        $tipView  = new TipView(14, '', '', [], []);
        $typeView = new TypeView(56, '', '');
        $type     = $this->prophesize(Type::class);
        $tip      = $this->prophesize(Tip::class);

        $this->tipRepository->getById(14)->shouldBeCalled()->willReturn($tip->reveal());
        $this->typeRepository->getById(56)->shouldBeCalled()->willReturn($type->reveal());

        $this->tipRepository->set($tip->reveal())->shouldBeCalled();
        $this->eventDispatcher
            ->dispatch(Events::TIP_ASSIGNED, new AssignedEvent($this->event->reveal(), $tip->reveal()))
            ->shouldBeCalled()
        ;

        $command        = new Affect($this->event->reveal());
        $command->tip   = $tipView;
        $command->types = [$typeView];

        $handler = new AffectHandler(
            $this->tipRepository->reveal(),
            $this->typeRepository->reveal(),
            $this->eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testTipNotFoundException()
    {
        $this->expectException(TipNotFoundException::class);

        $typeView = $this->prophesize(TypeView::class);
        $tipView  = new TipView(14, '', '', [], []);

        $this->tipRepository->getById(14)->shouldBeCalled()->willReturn(null);
        $this->typeRepository->getById(Argument::any())->shouldNotBeCalled();
        $this->tipRepository->set(Argument::any())->shouldNotBeCalled();

        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $command        = new Affect($this->event->reveal());
        $command->tip   = $tipView;
        $command->types = [$typeView];

        $handler = new AffectHandler(
            $this->tipRepository->reveal(),
            $this->typeRepository->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
