<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Meeting\MeetingDDayViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingDDayViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingDdayView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class MeetingDDayViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';

        $meeting        = $this->prophesize(Meeting::class);
        $participantOne = $this->prophesize(Participant::class);
        $participantTwo = $this->prophesize(Participant::class);
        $slot           = $this->prophesize(MeetingSlot::class);
        $spot           = $this->prophesize(Spot::class);
        $event          = $this->prophesize(Event::class);

        $meeting->getToParticipants()->willReturn([$participantOne->reveal(), $participantTwo->reveal()]);
        $meeting->getSlot()->willReturn($slot->reveal());
        $meeting->getSpot()->willReturn($spot->reveal());
        $meeting->getEvent()->willReturn($event->reveal());
        $slot->getBegin()->willReturn(new \DateTime('2017-01-01 09:00:00'));
        $spot->getReference()->willReturn('G30');
        $event->getTimeZone()->willReturn('Europe/Paris');

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);

        $participantInfoGuesser
            ->guessParticipantCompleteName($participantOne->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn('Vincent Dupond');

        $participantInfoGuesser
            ->guessParticipantCompleteName($participantTwo->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn('Antoine Martin');

        // Expected
        $expectedView = new MeetingDdayView(
            new \DateTime('2017-01-01 09:00:00'),
            'G30',
            'Europe/Paris',
            $locale,
            ['Vincent Dupond', 'Antoine Martin']
        );

        $handler = new MeetingDDayViewQueryHandler($participantInfoGuesser->reveal());

        $result = $handler->handle(new MeetingDDayViewQuery($meeting->reveal(), $locale));

        $this->assertEquals($expectedView, $result);
    }
}
