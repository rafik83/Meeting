<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class HappeningParticipantViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $locale      = 'fr';
        $category    = new Happening\Category($event, 'picto', 1, '#000', '#fff');
        $begin       = new \DateTime();
        $end         = new \DateTime();
        $user        = new User('john@.com', 'salt', 'password', $locale);
        $sheet       = SheetFactory::create($event, $user);
        $participant = new Participant($sheet, $user, [], true);
        $happening   = new Happening($event, $begin, $end, $category);

        $participation = new HappeningParticipation($happening, $participant);
        $happening->setParticipations([$participation]);

        // Mock
        $happeningRepository            = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningParticipantRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $questionRepository             = $this->prophesize(QuestionRepositoryInterface::class);
        $participantInfoGuesser         = $this->prophesize(ParticipantInfoGuesser::class);
        $sheetInfoGuesser               = $this->prophesize(SheetInfoGuesser::class);

        $happeningRepository->findByEvent($event)->shouldBeCalled()->willReturn([$happening]);

        $participantInfoGuesser->guessParticipantInfos($participant, $locale)
            ->shouldBeCalled()
            ->willReturn([
                Tag::PARTICIPANT_FIRSTNAME => 'john',
                Tag::PARTICIPANT_LASTNAME  => 'doh',
                Tag::PARTICIPANT_POSITION  => 'ceo',
            ]);

        $sheetInfoGuesser->guessSheetTitle($sheet, $locale)->shouldBeCalled()->willReturn('sheetName');

        $questionRepository->findByHappeningAndSheet($happening, $sheet)->shouldBeCalled()->willReturn(null);

        $handler = new HappeningParticipantViewQueryHandler(
            $happeningRepository->reveal(),
            $happeningParticipantRepository->reveal(),
            $questionRepository->reveal(),
            $participantInfoGuesser->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $happeningParticipantListView = $handler->handle(
            new HappeningParticipantViewQuery($event, $locale)
        );

        $this->assertCount(1, $happeningParticipantListView->getHappeningParticipantListView());
        $happeningParticipantView = $happeningParticipantListView->getHappeningParticipantListView()[0];

        $this->assertEquals('john', $happeningParticipantView->getFirstname());
        $this->assertEquals('doh', $happeningParticipantView->getLastname());
        $this->assertEquals('ceo', $happeningParticipantView->getPosition());
    }

    public function testHandleEmptyParticipation()
    {
        $this->expectException(EmptyHappeningParticipationException::class);

        $event       = EventFactory::createEvent();
        $locale      = 'fr';
        $category    = new Happening\Category($event, 'picto', 1, '#000', '#fff');
        $begin       = new \DateTime();
        $end         = new \DateTime();
        $happening   = new Happening($event, $begin, $end, $category);

        // Mock
        $happeningRepository            = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningParticipantRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $questionRepository             = $this->prophesize(QuestionRepositoryInterface::class);
        $participantInfoGuesser         = $this->prophesize(ParticipantInfoGuesser::class);
        $sheetInfoGuesser               = $this->prophesize(SheetInfoGuesser::class);

        $happeningRepository->findByEvent($event)->shouldBeCalled()->willReturn([$happening]);

        $handler = new HappeningParticipantViewQueryHandler(
            $happeningRepository->reveal(),
            $happeningParticipantRepository->reveal(),
            $questionRepository->reveal(),
            $participantInfoGuesser->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $happeningParticipantListView = $handler->handle(
            new HappeningParticipantViewQuery($event, $locale)
        );
    }
}
