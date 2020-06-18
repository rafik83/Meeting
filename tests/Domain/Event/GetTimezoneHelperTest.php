<?php

namespace Proximum\Vimeet\Tests\Domain\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Participant\ParticipantGuesser;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;

class GetTimezoneHelperTest extends TestCase
{
    /** @var ObjectProphecy|IsParticipantVisio */
    private $isParticipantVisio;

    /** @var ObjectProphecy|ParticipantGuesser */
    private $participantGuesser;

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    protected function setUp()
    {
        $this->isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $this->participantGuesser = $this->prophesize(ParticipantGuesser::class);

        $this->getTimezoneHelper = new GetTimezoneHelper(
            $this->isParticipantVisio->reveal(),
            $this->participantGuesser->reveal()
        );
    }

    public function testGetTimezoneByEventAndUser()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getTimezone()->shouldBeCalled()->willReturn('Europe/London');

        $this->isParticipantVisio->isSatisfiedBy($participant)->shouldBeCalled()->willReturn(true);

        $this->participantGuesser
            ->getUserEventParticipant($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($participant->reveal());

        $this->assertEquals(
            'Europe/London',
            $this->getTimezoneHelper->getTimezoneByEventAndUser($event->reveal(), $user->reveal())
        );
    }

    public function testGetTimezoneByEventAndParticipant()
    {
        $event = $this->prophesize(Event::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getTimezone()->shouldBeCalled()->willReturn('America/Guadeloupe');

        $this->isParticipantVisio->isSatisfiedBy($participant)->shouldBeCalled()->willReturn(true);

        $this->assertEquals(
            'America/Guadeloupe',
            $this->getTimezoneHelper->getTimezoneByEventAndParticipant($event->reveal(), $participant->reveal())
        );
    }

    public function testGetEventTimezone()
    {
        $event = $this->prophesize(Event::class);
        $event->getTimezone()->shouldBeCalled()->willReturn('America/New_York');

        $participant = $this->prophesize(Participant::class);

        $this->isParticipantVisio->isSatisfiedBy($participant)->shouldBeCalled()->willReturn(false);

        $this->assertEquals(
            'America/New_York',
            $this->getTimezoneHelper->getTimezoneByEventAndParticipant($event->reveal(), $participant->reveal())
        );
    }
}
