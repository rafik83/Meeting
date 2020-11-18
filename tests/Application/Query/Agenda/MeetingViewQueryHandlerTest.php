<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Security\VideoMeetingAccess;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingOwnSheetParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\SheetMetView;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Helper\LinkedSheetsTitle;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingViewQueryHandlerTest extends TestCase
{
    public function testHandleFrom(): void
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setColors(
            'leftColor',
            'rightColor',
            'textColor',
            'headerLeftColor',
            'headerRightColor',
            'backgroundColor',
            '#2F2F2F',
            '#2F2F2F',
            '#FFF'
        );
        $user = UserFactory::create();
        $type = new Type($event);

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $linkedSheets = $this->prophesize(Sheet\LinkedSheets::class);
        $sheetMetView = new SheetMetView('sheetMetTitle', true);
        $sheetMetView2 = new SheetMetView('sheetMetLinkedSheet', false);

        $sheetMetLinkedSheet = $this->prophesize(Sheet::class);
        $sheetMetLinkedSheet->getTitle()->willReturn('sheetMetLinkedSheet');

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(42);
        $sheet->getType()->willReturn($type);
        $sheet->getTitle()->willReturn('Korben Dallas LTD');
        $sheet->hasOnlyOneParticipant()->shouldBeCalled()->willReturn(true);

        $sheetMet = $this->prophesize(Sheet::class);
        $sheetMet->hasLinkedSheets()->willReturn(true);
        $sheetMet->getLinkedSheets()->willReturn($linkedSheets->reveal());
        $sheetMet->getType()->willReturn($type);
        $sheetMet->getId()->willReturn(2);
        $sheetMet->getTitle()->willReturn('sheetMetTitle');

        $linkedSheets->getSheets()->willReturn([$sheetMet->reveal(), $sheetMetLinkedSheet->reveal()]);

        $requestRepository
            ->hasApprovedMeetingRequest($sheet->reveal(), $sheetMetLinkedSheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $requestRepository
            ->hasApprovedMeetingRequest($sheet->reveal(), $sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->willReturn(24);
        $sheet->getUserParticipant($user)->willReturn($participant->reveal());
        $participant2 = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user1);
        $participant2->getUser()->willReturn($user2);
        $user1->getId()->willReturn(334455);
        $user2->getId()->willReturn(112233);
        $request   = new Request($sheet->reveal(), [], $sheetMet->reveal(), [], new \DateTime(), $user, $event);
        $begin     = new \DateTime('2016-10-12 10:00:00.000');
        $end       = new \DateTime('2016-10-12 12:00:00.000');
        $slot      = new MeetingSlot($event, $begin, $end, false);
        $spot      = new Spot('ref', $event, 1, 2, 3, true);
        $rule      = new Rule($event, $type, $type, Tag::getAll(), 0);
        $cardView  = new CardView(1, false, 'firstName1', 'lastName1', 'position1', 'avatar1', false, 2);
        $cardView2 = new CardView(2, false, 'firstName2', 'lastName2', 'position2', 'avatar2', false, 2);

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

        $ownUser = $this->prophesize(User::class);
        $ownParticipant = $this->prophesize(Participant::class);
        $ownParticipant->getUser()->willReturn($ownUser);
        $ownUser->getId()->willReturn(556677);
        $meeting->getParticipants($sheet->reveal())->willReturn([$ownParticipant->reveal()]);

        $participantView1 = new MeetingParticipantView($cardView);
        $participantView2 = new MeetingParticipantView($cardView2);
        $participants     = [$participantView1, $participantView2];

        $participantHandler = $this->prophesize(MeetingParticipantViewQueryHandler::class);
        $videoMeetingAccess = $this->prophesize(VideoMeetingAccess::class);
        $linkedSheetsTitle = new LinkedSheetsTitle($requestRepository->reveal());

        $participantHandler
            ->handle(new MeetingParticipantViewQuery($participant->reveal(), [$rule], 'fr'))
            ->shouldBeCalled()
            ->willReturn($participantView1);
        $participantHandler
            ->handle(new MeetingParticipantViewQuery($participant2->reveal(), [$rule], 'fr'))
            ->shouldBeCalled()
            ->willReturn($participantView2);

        $videoMeetingAccess->allowedToAccess($meeting)->shouldBeCalled()->willReturn(false);

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessParticipantInfos($ownParticipant, 'fr')
            ->shouldBeCalled()
            ->willReturn([
                'participant_firstname' => 'Korben',
                'participant_lastname' => 'Dallas',
                'participant_position' => 'Taxi Driver',
            ])
        ;
        $participantInfos = [
            112233 => 'firstName2 lastName2 (position2) - sheetMetTitle, sheetMetLinkedSheet',
            334455 => 'firstName1 lastName1 (position1) - sheetMetTitle, sheetMetLinkedSheet',
            556677 => 'Korben Dallas (Taxi Driver) - Korben Dallas LTD',
        ];

        $meetingHandler = new MeetingViewQueryHandler(
            $participantHandler->reveal(),
            $videoMeetingAccess->reveal(),
            $linkedSheetsTitle,
            $participantInfoGuesser->reveal(),
            new \DateTime('2016-10-12 10:08:00.000')
        );

        $result   = $meetingHandler->handle(new MeetingViewQuery($meeting->reveal(), $sheet->reveal(), true, $user, $event, 'fr'));
        $expected = new MeetingView(
            1,
            42,
            24,
            'Korben Dallas LTD',
            2,
            [$sheetMetView, $sheetMetView2],
            [],
            $begin,
            $end,
            6720,
            1344,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            $participants,
            $participantInfos,
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleTo(): void
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setColors(
            'leftColor',
            'rightColor',
            'textColor',
            'headerLeftColor',
            'headerRightColor',
            'backgroundColor',
            '#2F2F2F',
            '#2F2F2F',
            '#FFF'
        );
        $user = UserFactory::create();
        $type = new Type($event);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(42);
        $sheet->getTitle()->willReturn('userSheetTitle');
        $sheet->getType()->willReturn($type);
        $sheet->hasOnlyOneParticipant()->shouldBeCalled()->willReturn(false);

        $sheetMet = $this->prophesize(Sheet::class);
        $sheetMet->getId()->willReturn(1);
        $sheetMet->getTitle()->willReturn('sheetMetTitle');
        $sheetMet->hasLinkedSheets()->willReturn(false);
        $sheetMet->getType()->willReturn($type);

        $sheetMetView = new SheetMetView('sheetMetTitle', false);

        $userParticipant1 = $this->prophesize(User::class);
        $userParticipant2 = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->willReturn(24);
        $sheet->getUserParticipant($user)->willReturn($participant->reveal());
        $participant2 = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($userParticipant1->reveal());
        $participant2->getUser()->willReturn($userParticipant2->reveal());
        $userParticipant1->getId()->willReturn(4321);
        $userParticipant2->getId()->willReturn(1234);

        $request   = new Request($sheetMet->reveal(), [], $sheet->reveal(), [], new \DateTime(), $user, $event);
        $begin     = new \DateTime('2016-10-12 10:00:00.000');
        $end       = new \DateTime('2016-10-12 12:00:00.000');
        $slot      = new MeetingSlot($event, $begin, $end, false);
        $spot      = new Spot('ref', $event, 1, 2, 3, true);
        $rule      = new Rule($event, $type, $type, Tag::getAll(), 0);
        $cardView  = new CardView(1, false, 'firstName1', 'lastName1', 'position1', 'avatar1', false, 2);
        $cardView2 = new CardView(2, false, 'firstName2', 'lastName2', 'position2', 'avatar2', false, 2);

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

        $ownUser = $this->prophesize(User::class);
        $ownParticipant = $this->prophesize(Participant::class);
        $ownParticipant->getUser()->willReturn($ownUser);
        $ownUser->getId()->willReturn(6789);
        $meeting->getParticipants($sheet->reveal())->willReturn([$ownParticipant->reveal()]);

        $participantView1 = new MeetingParticipantView($cardView);
        $participantView2 = new MeetingParticipantView($cardView2);
        $participants     = [$participantView1, $participantView2];

        $participantViewQuery1 = new MeetingParticipantViewQuery($participant->reveal(), [$rule], 'fr');
        $participantViewQuery2 = new MeetingParticipantViewQuery($participant2->reveal(), [$rule], 'fr');

        $participantHandler = $this->prophesize(MeetingParticipantViewQueryHandler::class);
        $videoMeetingAccess = $this->prophesize(VideoMeetingAccess::class);
        $requestRepository  = $this->prophesize(RequestRepositoryInterface::class);
        $linkedSheetsTitle = new LinkedSheetsTitle($requestRepository->reveal());

        $participantHandler
            ->handle($participantViewQuery1)
            ->shouldBeCalled()
            ->willReturn($participantView1);
        $participantHandler
            ->handle($participantViewQuery2)
            ->shouldBeCalled()
            ->willReturn($participantView2);

        $videoMeetingAccess->allowedToAccess($meeting)->shouldBeCalled()->willReturn(false);

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessParticipantInfos($ownParticipant, 'fr')
            ->shouldBeCalled()
            ->willReturn([
                'participant_firstname' => 'Korben',
                'participant_lastname' => 'Dallas',
                'participant_position' => 'Developer',
            ])
        ;


        $participantInfos = [
            6789 => 'Korben Dallas (Developer) - userSheetTitle',
            1234 => 'firstName2 lastName2 (position2) - sheetMetTitle',
            4321 => 'firstName1 lastName1 (position1) - sheetMetTitle',
        ];

        $meetingHandler = new MeetingViewQueryHandler(
            $participantHandler->reveal(),
            $videoMeetingAccess->reveal(),
            $linkedSheetsTitle,
            $participantInfoGuesser->reveal(),
            new \DateTime('2016-10-12 10:05:00.000')
        );

        $result   = $meetingHandler->handle(new MeetingViewQuery($meeting->reveal(), $sheet->reveal(), true, $user, $event, 'fr'));
        $expected = new MeetingView(
            1,
            42,
            24,
            'userSheetTitle',
            1,
            [$sheetMetView],
            [
                new MeetingOwnSheetParticipantView('Korben', 'Dallas', 'Developer'),
            ],
            $begin,
            $end,
            6900,
            1380,
            'ref',
            'Europe/Paris',
            'leftColor',
            'rightColor',
            $participants,
            $participantInfos,
            true
        );

        $this->assertEquals($expected, $result);
    }
}
