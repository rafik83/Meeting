<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\ParticipantPresenceCommand;
use Proximum\Vimeet\Application\Command\Meeting\ParticipantPresenceCommandHandler;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\ParticipantExtraDataRepositoryInterface;

class ParticipantPresenceCommandHandlerTest extends TestCase
{
    public function testHandleNewParticipantPresence(): void
    {
        $participantExtraDataRepository = $this->prophesize(ParticipantExtraDataRepositoryInterface::class);
        $participant = $this->prophesize(Participant::class);
        $meeting = $this->prophesize(Meeting::class);
        $date = new \DateTime();

        $participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant->reveal(),
            $meeting->reveal(),
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        )->shouldBeCalled()->willReturn(null);

        $participantExtraDataRepository->add(
            new Meeting\ParticipantExtraData(
                Meeting\ParticipantExtraData::TYPE_PRESENCE,
                $participant->reveal(),
                $meeting->reveal(),
                $date
            )
        )->shouldBeCalled();

        $handler = new ParticipantPresenceCommandHandler(
            $participantExtraDataRepository->reveal(),
            $date
        );
        $handler->handle(new ParticipantPresenceCommand(
            $participant->reveal(),
            $meeting->reveal()
        ));
    }

    public function testHandleParticipantAlreadyPresent(): void
    {
        $participantExtraDataRepository = $this->prophesize(ParticipantExtraDataRepositoryInterface::class);
        $participant = $this->prophesize(Participant::class);
        $participantExtraData = $this->prophesize(Meeting\ParticipantExtraData::class);
        $meeting = $this->prophesize(Meeting::class);
        $date = new \DateTime();

        $participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant->reveal(),
            $meeting->reveal(),
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        )->shouldBeCalled()->willReturn($participantExtraData->reveal());

        $participantExtraData->setDate($date)->shouldBeCalled();
        $participantExtraDataRepository->set($participantExtraData->reveal())->shouldBeCalled();

        $handler = new ParticipantPresenceCommandHandler(
            $participantExtraDataRepository->reveal(),
            $date
        );
        $handler->handle(new ParticipantPresenceCommand(
            $participant->reveal(),
            $meeting->reveal()
        ));
    }
}
