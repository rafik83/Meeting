<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting\Admin\ListMeeting;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\MeetingView;
use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;

class MeetingViewQueryHandlerTest extends TestCase
{
    public function testHandle():void
    {
        $locale = 'fr';
        $sheetA = $this->prophesize(Sheet::class);
        $sheetB = $this->prophesize(Sheet::class);
        $participantA1 = $this->prophesize(Participant::class);
        $participantA2 = $this->prophesize(Participant::class);
        $participantB1 = $this->prophesize(Participant::class);
        $spot = $this->prophesize(Spot::class);
        $slot = $this->prophesize(MeetingSlot::class);
        $spot->getReference()->willReturn('RefA');
        $event = $this->prophesize(Event::class);

        $meeting = $this->prophesize(Meeting::class);
        $meeting->getId()->willReturn(12);
        $meeting->getFromSheet()->willReturn($sheetA->reveal());
        $meeting->getToSheet()->willReturn($sheetB->reveal());
        $meeting->getStatus()->willReturn(Meeting::STATUS_CONFIRMED);
        $meeting->getFromParticipants()->willReturn(
            new ArrayCollection([$participantA1->reveal(), $participantA2->reveal()])
        );
        $meeting->getToParticipants()->willReturn(
            new ArrayCollection([$participantB1->reveal()])
        );
        $meeting->getEvent()->willReturn($event->reveal());
        $meeting->getSpot()->willReturn($spot->reveal());
        $meeting->getSlot()->willReturn($slot->reveal());

        $pv1 = new ParticipantView(18, 'ParticipantA1');
        $pv2 = new ParticipantView(20, 'ParticipantA2');
        $pv3 = new ParticipantView(22, 'ParticipantB1');

        $participantViewQueryHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $participantViewQueryHandler
            ->handle(new ParticipantViewQuery($participantA1->reveal(), $event->reveal(), $meeting->reveal(), $slot->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($pv1)
        ;
        $participantViewQueryHandler
            ->handle(new ParticipantViewQuery($participantA2->reveal(), $event->reveal(), $meeting->reveal(), $slot->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($pv2)
        ;
        $participantViewQueryHandler
            ->handle(new ParticipantViewQuery($participantB1->reveal(), $event->reveal(), $meeting->reveal(), $slot->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($pv3)
        ;

        $sheetA->getId()->shouldBeCalled()->willReturn(14);
        $sheetA->getTitle()->shouldBeCalled()->willReturn('SheetA');

        $sheetB->getId()->shouldBeCalled()->willReturn(16);
        $sheetB->getTitle()->shouldBeCalled()->willReturn('SheetB');

        $query = new MeetingViewQuery($meeting->reveal(), $locale);
        $handler = new MeetingViewQueryHandler(
            $participantViewQueryHandler->reveal()
        );

        $result = $handler->handle($query);

        $expected = new MeetingView(
            12,
            'RefA',
            14,
            'SheetA',
            [
                $pv1,
                $pv2,
            ],
            16,
            'SheetB',
            [
                $pv3,
            ],
            Meeting::STATUS_CONFIRMED
        );

        $this->assertEquals($expected, $result);
    }
}
