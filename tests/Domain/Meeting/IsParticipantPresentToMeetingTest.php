<?php

namespace Proximum\Vimeet\Tests\Domain\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Meeting\IsParticipantPresentToMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\Meeting\ParticipantExtraDataRepositoryInterface;

class IsParticipantPresentToMeetingTest extends TestCase
{
    public function testIsSatisfiedBy(): void
    {
        $participantExtraDataRepository = $this->prophesize(ParticipantExtraDataRepositoryInterface::class);
        $participant = $this->prophesize(Participant::class);
        $participantExtraData = $this->prophesize(Meeting\ParticipantExtraData::class);
        $participantExtraData->getDate()->shouldBeCalled()->willReturn(new \DateTime('2018-08-22 10:08:00.000'));
        $meeting = $this->prophesize(Meeting::class);
        $date = new \DateTime('2018-08-22 10:10:00.000');

        $participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant->reveal(),
            $meeting->reveal(),
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        )->shouldBeCalled()->willReturn($participantExtraData);

        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant->reveal())->willReturn(true);

        $pattern = new IsParticipantPresentToMeeting(
            $participantExtraDataRepository->reveal(),
            $date,
            $isParticipantVisio->reveal()
        );

        $this->assertTrue($pattern->isSatisfiedBy($participant->reveal(), $meeting->reveal()));
    }

    public function testIsNotSatisfiedByDateComparison(): void
    {
        $participantExtraDataRepository = $this->prophesize(ParticipantExtraDataRepositoryInterface::class);
        $participant = $this->prophesize(Participant::class);
        $participantExtraData = $this->prophesize(Meeting\ParticipantExtraData::class);
        $participantExtraData->getDate()->shouldBeCalled()->willReturn(new \DateTime('2018-08-22 10:06:00.000'));
        $meeting = $this->prophesize(Meeting::class);
        $date = new \DateTime('2018-08-22 10:10:00.000');

        $participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant->reveal(),
            $meeting->reveal(),
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        )->shouldBeCalled()->willReturn($participantExtraData);

        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant->reveal())->willReturn(true);

        $pattern = new IsParticipantPresentToMeeting(
            $participantExtraDataRepository->reveal(),
            $date,
            $isParticipantVisio->reveal()
        );

        $this->assertFalse($pattern->isSatisfiedBy($participant->reveal(), $meeting->reveal()));
    }

    public function testIsNotSatisfiedByEmptyResult(): void
    {
        $participantExtraDataRepository = $this->prophesize(ParticipantExtraDataRepositoryInterface::class);
        $participant = $this->prophesize(Participant::class);
        $meeting = $this->prophesize(Meeting::class);
        $date = new \DateTime();

        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant->reveal())->willReturn(true);

        $participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant->reveal(),
            $meeting->reveal(),
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        )->shouldBeCalled()->willReturn(null);

        $pattern = new IsParticipantPresentToMeeting(
            $participantExtraDataRepository->reveal(),
            $date,
            $isParticipantVisio->reveal()
        );

        $this->assertFalse($pattern->isSatisfiedBy($participant->reveal(), $meeting->reveal()));
    }

    public function testIsNotSatisfiedByNonVisioParticipant(): void
    {
        $participantExtraDataRepository = $this->prophesize(ParticipantExtraDataRepositoryInterface::class);
        $participant = $this->prophesize(Participant::class);
        $meeting = $this->prophesize(Meeting::class);
        $date = new \DateTime();

        $participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant->reveal(),
            $meeting->reveal(),
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        )->shouldNotBeCalled();

        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant->reveal())->willReturn(false);

        $pattern = new IsParticipantPresentToMeeting(
            $participantExtraDataRepository->reveal(),
            $date,
            $isParticipantVisio->reveal()
        );

        $this->assertFalse($pattern->isSatisfiedBy($participant->reveal(), $meeting->reveal()));
    }
}
