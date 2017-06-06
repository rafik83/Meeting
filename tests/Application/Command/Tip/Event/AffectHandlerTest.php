<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Exception\Tip\TipAlreadyAffectedToEventException;
use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Application\Exception\Tip\TipTranslationNotAvailableForEventException;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Tests\Factory\TipFactory;
use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Application\Command\Tip\Event\AffectHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AffectHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AffectHandler */
    private $handler;
    
    /** @var TipRepositoryInterface */
    private $tipRepository;
    
    /** @var TypeRepositoryInterface */
    private $typeRepository;
    
    /** @var Tip */
    private $tip;

    /** @var Type */
    private $type;

    /** @var Affect */
    private $command;
    
    public function setUp()
    {
        $event    = EventFactory::createEvent();
        $tip      = TipFactory::createTip('awesome tip');
        $type     = new Type($event);
        $typeView = new TypeView($type->getId(), $type->getTitle('fr'), $type->getDescription('fr'));

        $tipTranslationView = new TipTranslationView(
            $tip->getId(),
            $tip->getTranslationTitle('fr'),
            $tip->getTranslationContent('fr'),
            'admin_title'
        );

        $command        = new Affect($event);
        $command->tip   = $tipTranslationView;
        $command->types = [$typeView];

        $this->tipRepository  = $this->prophesize(TipRepositoryInterface::class);
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $this->tip            = $tip;
        $this->type           = $type;
        $this->command        = $command;
        $this->handler        = new AffectHandler($this->tipRepository->reveal(), $this->typeRepository->reveal());
    }
    public function testHandle()
    {
        $this->tipRepository->getByTipTranslationId(null)->shouldBeCalled()->willReturn($this->tip);

        $this->tipRepository->isTipAffectedToEvent($this->tip, $this->command->event)->shouldBeCalled()->willReturn(false);

        $this->typeRepository->getById(null)->shouldBeCalled()->willReturn($this->type);

        $this->tipRepository->setTypes($this->tip)->shouldBeCalled();

        $this->handler->handle($this->command);
    }

    public function testIsAlreadyAffectedOnEventException()
    {
        $this->tipRepository->getByTipTranslationId(null)->shouldBeCalled()->willReturn($this->tip);

        $this->tipRepository->isTipAffectedToEvent($this->tip, $this->command->event)->shouldBeCalled()->willReturn(true);

        $this->typeRepository->getById(null)->shouldNotBeCalled();

        $this->tipRepository->setTypes($this->tip)->shouldNotBeCalled();

        $this->expectException(TipAlreadyAffectedToEventException::class);

        $this->handler->handle($this->command);
    }

    public function testTipNotFoundException()
    {
        $this->tipRepository->getByTipTranslationId(null)->shouldBeCalled()->willReturn(null);

        $this->expectException(TipNotFoundException::class);

        $this->tipRepository->isTipAffectedToEvent($this->tip, $this->command->event)->shouldNotBeCalled();

        $this->typeRepository->getById(null)->shouldNotBeCalled();

        $this->tipRepository->setTypes($this->tip)->shouldNotBeCalled();

        $this->handler->handle($this->command);
    }

    public function testTipTranslationNotavailableForEventException()
    {
        $this->tip = TipFactory::createTip(
            'unavailable_tip',
            [
                'onMeetingManagement' => true,
                'onCatalog'           => true,
                'onPrintPlanning'     => true,
                'onSheet'             => true,
                'onAgenda'            => true,
                'onProgram'           => true,
            ],
            ['en', 'it']
        );

        $this->tipRepository->getByTipTranslationId(null)->shouldBeCalled()->willReturn($this->tip);

        $this->expectException(TipTranslationNotAvailableForEventException::class);

        $this->tipRepository->isTipAffectedToEvent($this->tip, $this->command->event)->shouldNotBeCalled();

        $this->typeRepository->getById(null)->shouldNotBeCalled();

        $this->tipRepository->setTypes($this->tip)->shouldNotBeCalled();

        $this->handler->handle($this->command);
    }
}
