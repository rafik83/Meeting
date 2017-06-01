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
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
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
    public function testHandle()
    {
        $tipRepository  = $this->prophesize(TipRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $event = EventFactory::createEvent();
        $tip   = TipFactory::createTip('awesome tip');
        $type  = new Type($event);

        $tipTranslationView = new TipTranslationView(
            $tip->getId(),
            $tip->getTranslationTitle('fr'),
            $tip->getTranslationContent('fr'),
            'admin_title'
        );
        $typeView = new TypeView($type->getId(), $type->getTitle('fr'), $type->getDescription('fr'));

        $command = new Affect($event);
        $command->tip   = $tipTranslationView;
        $command->types = [$typeView];


        $handler = new AffectHandler($tipRepository->reveal(), $typeRepository->reveal());

        $tipRepository->getByTipTranslationId(null, $command->event)->shouldBeCalled()->willReturn($tip);

        $typeRepository->getById(null)->shouldBeCalled()->willReturn($type);

        $tipRepository->setTypes($tip)->shouldBeCalled();

        $handler->handle($command);
    }

    public function testIsAlreadyAffectedOnEventException()
    {
        $tipRepository  = $this->prophesize(TipRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $event = EventFactory::createEvent();
        $tip   = TipFactory::createTip('awesome tip');
        $type  = new Type($event);

        $tipTranslationView = new TipTranslationView(
            $tip->getId(),
            $tip->getTranslationTitle('fr'),
            $tip->getTranslationContent('fr'),
            'admin_title'
        );
        $typeView = new TypeView($type->getId(), $type->getTitle('fr'), $type->getDescription('fr'));

        $command = new Affect($event);
        $command->tip   = $tipTranslationView;
        $command->types = [$typeView];


        $handler = new AffectHandler($tipRepository->reveal(), $typeRepository->reveal());

        $tipRepository->getByTipTranslationId(null, $command->event)->shouldBeCalled()->willReturn($tip);

        $typeRepository->getById(null)->shouldBeCalled()->willReturn($type);

        $tipRepository->setTypes($tip)->shouldBeCalled();

        $handler->handle($command);

        $this->expectException(TipAlreadyAffectedToEventException::class);

        $handler->handle($command);
    }
}
