<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Meeting\IsParticipantPresentToMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
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

        $pattern = new IsParticipantPresentToMeeting(
            $participantExtraDataRepository->reveal(),
            $date
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

        $pattern = new IsParticipantPresentToMeeting(
            $participantExtraDataRepository->reveal(),
            $date
        );

        $this->assertFalse($pattern->isSatisfiedBy($participant->reveal(), $meeting->reveal()));
    }

    public function testIsNotSatisfiedByEmptyResult(): void
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

        $pattern = new IsParticipantPresentToMeeting(
            $participantExtraDataRepository->reveal(),
            $date
        );

        $this->assertFalse($pattern->isSatisfiedBy($participant->reveal(), $meeting->reveal()));
    }
}
