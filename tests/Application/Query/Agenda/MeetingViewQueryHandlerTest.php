<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Components\Security\VideoMeetingAccess;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingParticipantView;
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
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingViewQueryHandlerTest extends TestCase
{
    public function testHandleFrom()
    {
        $event        = EventFactory::createEvent();
        $event->getConfiguration()->setColors('leftColor', 'rightColor', 'textColor', 'backgroundColor');
        $user         = UserFactory::create();
        $type         = new Type($event);

        $sheet = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);

        $sheet->getType()->willReturn($type);
        $sheetMet->getType()->willReturn($type);

        $sheetMet->getId()->willReturn(2);

        $sheet->getTitle()->willReturn('userSheetTitle');
        $sheetMet->getTitle()->willReturn('sheetMetTitle');

        $participant  = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $request      = new Request($sheet->reveal(), [], $sheetMet->reveal(), [], new \DateTime(), $user, $event);
        $begin        = new \DateTime('2016-10-12 10:00:00.000');
        $end          = new \DateTime('2016-10-12 12:00:00.000');
        $slot         = new MeetingSlot($event, $begin, $end, false);
        $spot         = new Spot('ref', $event, 1, 2, 3, true);
        $rule         = new Rule($event, $type, $type, [], 1);
        $cardView     = new CardView(1, false, 'firstName1', 'lastName1', 'position1', 'avatar1', false, 2);
        $cardView2    = new CardView(2, false, 'firstName2', 'lastName2', 'position2', 'avatar2', false, 2);

        $meeting = $this->prophesize(Meeting::class);
        $meeting->getId()->willReturn(1);
        $meeting->getRequest()->willReturn($request);
        $meeting->getSlot()->willReturn($slot);
        $meeting->getFromSheet()->willReturn($sheet->reveal());
        $meeting->getFromParticipants()->willReturn([]);
        $meeting->getToSheet()->willReturn($sheetMet->reveal());
        $meeting->getToParticipants()->willReturn([$participant->reveal(), $participant2->reveal()]);
        $meeting->getCreatedAt()->willReturn(new \DateTime());
        $meeting->getSpot()->willReturn($spot);
        $meeting->getEvent()->willReturn($event);
        $meeting->getSheetOfUser($user)->willReturn($sheet);
        $meeting->getSheetMet($sheet)->willReturn($sheetMet->reveal());
        $meeting->getParticipants($sheetMet->reveal())->willReturn([$participant->reveal(), $participant2->reveal()]);

        $participantView1 = new MeetingParticipantView($cardView);
        $participantView2 = new MeetingParticipantView($cardView2);
        $participants     = [$participantView1, $participantView2];

        $participantHandler = $this->prophesize(MeetingParticipantViewQueryHandler::class);
        $ruleRepository     = $this->prophesize(RuleRepositoryInterface::class);
        $videoMeetingAccess = $this->prophesize(VideoMeetingAccess::class);

        $participantHandler
            ->handle(new MeetingParticipantViewQuery($participant->reveal(), [$rule], 'fr'))
            ->shouldBeCalled()
            ->willReturn($participantView1);
        $participantHandler
            ->handle(new MeetingParticipantViewQuery($participant2->reveal(), [$rule], 'fr'))
            ->shouldBeCalled()
            ->willReturn($participantView2);

        $ruleRepository->getBySeerTypeAndSeeableType($type, $type)->shouldBeCalled()->willReturn([$rule]);

        $videoMeetingAccess->allowedToAccess($meeting)->shouldBeCalled()->willReturn(false);

        $meetingHandler = new MeetingViewQueryHandler(
            $participantHandler->reveal(),
            $ruleRepository->reveal(),
            $videoMeetingAccess->reveal()
        );

        $result   = $meetingHandler->handle(new MeetingViewQuery($meeting->reveal(), $sheet->reveal(), true, $user, $event, 'fr'));
        $expected = new MeetingView(
            1,
            'userSheetTitle',
            2,
            'sheetMetTitle',
            $begin,
            $end,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            $participants,
            true,
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleTo()
    {
        $event        = EventFactory::createEvent();
        $event->getConfiguration()->setColors('leftColor', 'rightColor', 'textColor', 'backgroundColor');
        $user         = UserFactory::create();
        $type         = new Type($event);
        $sheet    = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type);
        $sheetMet->getType()->willReturn($type);

        $sheetMet->getId()->willReturn(1);

        $sheet->getTitle()->willReturn('userSheetTitle');
        $sheetMet->getTitle()->willReturn('sheetMetTitle');

        $participant  = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $request      = new Request($sheetMet->reveal(), [], $sheet->reveal(), [], new \DateTime(), $user, $event);
        $begin        = new \DateTime('2016-10-12 10:00:00.000');
        $end          = new \DateTime('2016-10-12 12:00:00.000');
        $slot         = new MeetingSlot($event, $begin, $end, false);
        $spot         = new Spot('ref', $event, 1, 2, 3, true);
        $rule         = new Rule($event, $type, $type, [], 1);
        $cardView     = new CardView(1, false, 'firstName1', 'lastName1', 'position1', 'avatar1', false, 2);
        $cardView2    = new CardView(2, false, 'firstName2', 'lastName2', 'position2', 'avatar2', false, 2);

        $meeting = $this->prophesize(Meeting::class);
        $meeting->getId()->willReturn(1);
        $meeting->getRequest()->willReturn($request);
        $meeting->getSlot()->willReturn($slot);
        $meeting->getFromSheet()->willReturn($sheet->reveal());
        $meeting->getFromParticipants()->willReturn([]);
        $meeting->getToSheet()->willReturn($sheetMet->reveal());
        $meeting->getToParticipants()->willReturn([$participant->reveal(), $participant2->reveal()]);
        $meeting->getCreatedAt()->willReturn(new \DateTime());
        $meeting->getSpot()->willReturn($spot);
        $meeting->getEvent()->willReturn($event);
        $meeting->getSheetOfUser($user)->willReturn($sheet);
        $meeting->getSheetMet($sheet)->willReturn($sheetMet->reveal());
        $meeting->getParticipants($sheetMet->reveal())->willReturn([$participant->reveal(), $participant2->reveal()]);

        $participantView1 = new MeetingParticipantView($cardView);
        $participantView2 = new MeetingParticipantView($cardView2);
        $participants     = [$participantView1, $participantView2];

        $participantViewQuery1 = new MeetingParticipantViewQuery($participant->reveal(), [$rule], 'fr');
        $participantViewQuery2 = new MeetingParticipantViewQuery($participant2->reveal(), [$rule], 'fr');

        $participantHandler = $this->prophesize(MeetingParticipantViewQueryHandler::class);
        $ruleRepository     = $this->prophesize(RuleRepositoryInterface::class);
        $videoMeetingAccess = $this->prophesize(VideoMeetingAccess::class);

        $participantHandler
            ->handle($participantViewQuery1)
            ->shouldBeCalled()
            ->willReturn($participantView1);
        $participantHandler
            ->handle($participantViewQuery2)
            ->shouldBeCalled()
            ->willReturn($participantView2);
        $ruleRepository->getBySeerTypeAndSeeableType($type, $type)->shouldBeCalled()->willReturn([$rule]);

        $videoMeetingAccess->allowedToAccess($meeting)->shouldBeCalled()->willReturn(false);

        $meetingHandler = new MeetingViewQueryHandler(
            $participantHandler->reveal(),
            $ruleRepository->reveal(),
            $videoMeetingAccess->reveal()
        );

        $result   = $meetingHandler->handle(new MeetingViewQuery($meeting->reveal(), $sheet->reveal(), true, $user, $event, 'fr'));
        $expected = new MeetingView(
            1,
            'userSheetTitle',
            1,
            'sheetMetTitle',
            $begin,
            $end,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            $participants,
            true,
            true
        );

        $this->assertEquals($expected, $result);
    }
}
