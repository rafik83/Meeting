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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
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
        $dateTime =new \DateTime();
        $this->event->getLocales()->willReturn(['fr']);
        $type = $this->prophesize(Type::class);
        $globalTip = $this->prophesize(Tip::class);
        $globalTip->getTitle()->willReturn('adminTitle');
        $globalTip->isOnMeetingManagement()->willReturn(true);
        $globalTip->isOnCatalog()->willReturn(false);
        $globalTip->isOnPrintPlanning()->willReturn(true);
        $globalTip->isOnSheet()->willReturn(false);
        $globalTip->isOnAgenda()->willReturn(true);
        $globalTip->isOnProgram()->willReturn(false);
        $globalTip->isOnConfirmationPhone()->willReturn(true);
        $globalTip->getTranslationTitle('fr')->willReturn('title');
        $globalTip->getTranslationContent('fr')->willReturn('content');

        $tip = new Tip(
            'adminTitle',
            $this->event->reveal(),
            true,
            false,
            true,
            false,
            true,
            false,
            true,
            $dateTime
        );

        $tip->translate(
            'fr',
            'toto',
            'content',
            $dateTime
        );
        $type->getId()->willReturn(12);
        $tip->setType($type->reveal());

        $this->tipRepository->add(Argument::that(function (Tip $tip) use ($type) {
            return $tip->getTitle() === 'adminTitle'
                && $tip->isOnMeetingManagement() === true
                && $tip->isOnCatalog() === false
                && $tip->isOnPrintPlanning() ===  true
                && $tip->isOnSheet() === false
                && $tip->isOnAgenda() === true
                && $tip->isOnProgram() === false
                && $tip->isOnConfirmationPhone() === true
                && $tip->getTranslationTitle('fr') === 'title'
                && $tip->getTranslationContent('fr') === 'content'
                && in_array($type->reveal(), $tip->getTypes(), true)
            ;
        }))->shouldBeCalled();
        $this->eventDispatcher
            ->dispatch(
                Events::TIP_ASSIGNED,
                Argument::that(function (AssignedEvent $event) {
                    return $event->getEvent() === $this->event->reveal()
                        && $event->getTip()->getTitle() === 'adminTitle';
                    })
            )->shouldBeCalled();

        $command        = new Affect($this->event->reveal());
        $command->tip   = $globalTip->reveal();
        $command->types = [$type->reveal()];

        $handler = new AffectHandler(
            $this->tipRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $dateTime
        );

        $handler->handle($command);
    }
}
