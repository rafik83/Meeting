<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planning\Day;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Application\View\Planning\Day\ParticipantMetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;

class MeetingViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $configuration;

    public function setUp()
    {
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesserCache::class);
        $this->event = $this->prophesize(Event::class);
        $this->configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->willReturn($this->configuration->reveal());
    }

    public function testHandle()
    {
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:30:00.000');
        $user = $this->prophesize(User::class);
        $meeting = $this->prophesize(Meeting::class);
        $sheetA = $this->prophesize(Sheet::class);
        $sheetB = $this->prophesize(Sheet::class);
        $spot = $this->prophesize(Spot::class);
        $slot = $this->prophesize(MeetingSlot::class);

        $meeting->getSheetOfUser($user->reveal())->willReturn($sheetA->reveal());
        $meeting->getSpot()->willReturn($spot->reveal());
        $meeting->getSheetMet($sheetA->reveal())->willReturn($sheetB->reveal());
        $meeting->getSlot()->willReturn($slot->reveal());
        $spot->getReference()->willReturn('A1');
        $slot->getBegin()->willReturn($begin);
        $slot->getEnd()->willReturn($end);
        $sheetA->getTitle()->willReturn('sheetA');
        $sheetB->getTitle()->willReturn('sheetB');

        $this->configuration->displayParticipantNameOnPlanning()->shouldBeCalled()->willReturn(true);
        $this->configuration->displayParticipantPositionOnPlanning()->shouldBeCalled()->willReturn(false);


        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $meeting
            ->getParticipants($sheetB->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;
        $this->participantInfoGuesser
            ->guessParticipantCompleteName($participant1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('prenom Nom 1')
        ;
        $this->participantInfoGuesser
            ->guessParticipantCompleteName($participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('prenom Nom 2')
        ;

        $handler = new MeetingViewQueryHandler(
            $this->participantInfoGuesser->reveal()
        );
        $result = $handler->handle(new MeetingViewQuery(
            $this->event->reveal(),
            $meeting->reveal(),
            $user->reveal(),
            'fr'
        ));

        $expected = new MeetingView(
            $begin,
            $end,
            'A1',
            true,
            [new ParticipantMetView('prenom Nom 1', null), new ParticipantMetView('prenom Nom 2', null)],
            $sheetA->reveal(),
            $sheetB->reveal()
        );

        $this->assertEquals($expected, $result);
    }
}
