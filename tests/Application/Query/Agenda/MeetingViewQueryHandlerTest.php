<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\ParticipantView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleFrom()
    {
        $event        = EventFactory::createEvent();
        $event->getConfiguration()->setColors('leftColor', 'rightColor', 'textColor');
        $user         = UserFactory::create();
        $type         = new Type($event);
        $sheet        = SheetFactory::create($event, $user, null, $type);
        $sheetMet     = SheetFactory::create($event, null, null, $type);
        $participant  = ParticipantFactory::create($sheetMet);
        $participant2 = ParticipantFactory::create($sheetMet);
        $request      = new Request($sheet, [], $sheetMet, [], new \DateTime(), $user);
        $begin        = new \DateTime('2016-10-12 10:00:00.000');
        $end          = new \DateTime('2016-10-12 12:00:00.000');
        $slot         = new MeetingSlot($event, $begin, $end, false);
        $spot         = new Spot('ref', $event, 1, 2, 3, true);
        $rule         = new Rule($event, $type, $type, [], 1);
        $cardView     = new CardView(1, false, 'firstName1', 'lastName1', 'position1', 'avatar1', false, 2);
        $cardView2    = new CardView(2, false, 'firstName2', 'lastName2', 'position2', 'avatar2', false, 2);
        $meeting      = new Meeting(
            $request,
            $slot,
            $sheet,
            [],
            $sheetMet,
            [$participant, $participant2],
            new \DateTime(),
            $spot
        );

        $participantView1 = new ParticipantView($cardView);
        $participantView2 = new ParticipantView($cardView2);
        $participants     = [$participantView1, $participantView2];

        $reflection = new \ReflectionClass(Sheet::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheetMet, 2);
        $property->setAccessible(false);

        $reflectionParticipant = new \ReflectionClass(Participant::class);
        $propertyParticipant   = $reflectionParticipant->getProperty('id');
        $propertyParticipant->setAccessible(true);
        $propertyParticipant->setValue($participant, 1);
        $propertyParticipant->setValue($participant2, 2);
        $propertyParticipant->setAccessible(false);

        $participantHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $sheetInfoGuesser   = $this->prophesize(SheetInfoGuesser::class);
        $ruleRepository     = $this->prophesize(RuleRepositoryInterface::class);
        $participantHandler
            ->handle(new ParticipantViewQuery($participant, [$rule], 'fr'))
            ->shouldBeCalled()
            ->willReturn($participantView1);
        $participantHandler
            ->handle(new ParticipantViewQuery($participant2, [$rule], 'fr'))
            ->shouldBeCalled()
            ->willReturn($participantView2);
        $sheetInfoGuesser->guessSheetTitle($sheetMet, 'fr')->shouldBeCalled()->willReturn('sheetMetTitle');
        $ruleRepository->getBySeerTypeAndSeeableType($type, $type)->shouldBeCalled()->willReturn([$rule]);

        $meetingHandler = new MeetingViewQueryHandler(
            $participantHandler->reveal(),
            $sheetInfoGuesser->reveal(),
            $ruleRepository->reveal()
        );

        $result   = $meetingHandler->handle(new MeetingViewQuery($meeting, $sheet, $event, 'fr'));
        $expected = new MeetingView(
            2,
            'sheetMetTitle',
            $begin,
            $end,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            $participants
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleTo()
    {
        $event        = EventFactory::createEvent();
        $event->getConfiguration()->setColors('leftColor', 'rightColor', 'textColor');
        $user         = UserFactory::create();
        $type         = new Type($event);
        $sheet        = SheetFactory::create($event, $user, null, $type);
        $sheetMet     = SheetFactory::create($event, null, null, $type);
        $participant  = ParticipantFactory::create($sheetMet);
        $participant2 = ParticipantFactory::create($sheetMet);
        $request      = new Request($sheetMet, [], $sheet, [], new \DateTime(), $user);
        $begin        = new \DateTime('2016-10-12 10:00:00.000');
        $end          = new \DateTime('2016-10-12 12:00:00.000');
        $slot         = new MeetingSlot($event, $begin, $end, false);
        $spot         = new Spot('ref', $event, 1, 2, 3, true);
        $rule         = new Rule($event, $type, $type, [], 1);
        $cardView     = new CardView(1, false, 'firstName1', 'lastName1', 'position1', 'avatar1', false, 2);
        $cardView2    = new CardView(2, false, 'firstName2', 'lastName2', 'position2', 'avatar2', false, 2);
        $meeting      = new Meeting(
            $request,
            $slot,
            $sheetMet,
            [$participant, $participant2],
            $sheet,
            [],
            new \DateTime(),
            $spot
        );

        $participantView1 = new ParticipantView($cardView);
        $participantView2 = new ParticipantView($cardView2);
        $participants     = [$participantView1, $participantView2];

        $participantViewQuery1 = new ParticipantViewQuery($participant, [$rule], 'fr');
        $participantViewQuery2 = new ParticipantViewQuery($participant2, [$rule], 'fr');

        $reflection = new \ReflectionClass(Sheet::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheetMet, 1);
        $property->setAccessible(false);

        $reflectionParticipant = new \ReflectionClass(Participant::class);
        $propertyParticipant   = $reflectionParticipant->getProperty('id');
        $propertyParticipant->setAccessible(true);
        $propertyParticipant->setValue($participant, 1);
        $propertyParticipant->setValue($participant2, 2);
        $propertyParticipant->setAccessible(false);

        $participantHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $sheetInfoGuesser   = $this->prophesize(SheetInfoGuesser::class);
        $ruleRepository     = $this->prophesize(RuleRepositoryInterface::class);
        $participantHandler
            ->handle($participantViewQuery1)
            ->shouldBeCalled()
            ->willReturn($participantView1);
        $participantHandler
            ->handle($participantViewQuery2)
            ->shouldBeCalled()
            ->willReturn($participantView2);
        $sheetInfoGuesser->guessSheetTitle($sheetMet, 'fr')->shouldBeCalled()->willReturn('sheetMetTitle');
        $ruleRepository->getBySeerTypeAndSeeableType($type, $type)->shouldBeCalled()->willReturn([$rule]);

        $meetingHandler = new MeetingViewQueryHandler(
            $participantHandler->reveal(),
            $sheetInfoGuesser->reveal(),
            $ruleRepository->reveal()
        );

        $result   = $meetingHandler->handle(new MeetingViewQuery($meeting, $sheet, $event, 'fr'));
        $expected = new MeetingView(
            1,
            'sheetMetTitle',
            $begin,
            $end,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            $participants
        );

        $this->assertEquals($expected, $result);
    }
}
