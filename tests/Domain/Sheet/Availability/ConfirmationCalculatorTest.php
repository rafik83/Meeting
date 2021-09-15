<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet\Availability;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Availability\ConfirmationCalculator;
use Proximum\Vimeet\Domain\Sheet\Availability\ConfirmationStatus;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ConfirmationCalculatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraDataRepository;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $participant1;

    /** @var ObjectProphecy */
    private $participant2;

    /** @var ObjectProphecy */
    private $participant3;

    /** @var ObjectProphecy */
    private $user1;

    /** @var ObjectProphecy */
    private $user2;

    /** @var ObjectProphecy */
    private $user3;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->event = $this->prophesize(Event::class);
        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant3 = $this->prophesize(Participant::class);
        $this->user1 = $this->prophesize(User::class);
        $this->user2 = $this->prophesize(User::class);
        $this->user3 = $this->prophesize(User::class);

        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getParticipantsArray()->willReturn([
            $this->participant1->reveal(),
            $this->participant2->reveal(),
            $this->participant3->reveal(),
        ]);
        $this->sheet->countParticipants()->willReturn(3);
        $this->participant1->getUser()->willReturn($this->user1->reveal());
        $this->participant2->getUser()->willReturn($this->user2->reveal());
        $this->participant3->getUser()->willReturn($this->user3->reveal());
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
    }

    public function testGetConfirmationStatusForSheetNoneConfirmed()
    {
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $calculator = new ConfirmationCalculator($this->extraDataRepository->reveal());
        $result = $calculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = ConfirmationStatus::NONE_CONFIRMED;

        $this->assertEquals($expected, $result);
    }

    public function testGetConfirmationStatusForSheetAllConfirmed()
    {
        $extraData1 = $this->prophesize(ExtraData::class);
        $extraData2 = $this->prophesize(ExtraData::class);
        $extraData3 = $this->prophesize(ExtraData::class);

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData1->reveal())
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData2->reveal())
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData3->reveal())
        ;
        $calculator = new ConfirmationCalculator($this->extraDataRepository->reveal());
        $result = $calculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = ConfirmationStatus::ALL_CONFIRMED;

        $this->assertEquals($expected, $result);
    }

    public function testGetConfirmationStatusForSheetAtLeastOneConfirmedWithSecondNull()
    {
        $extraData1 = $this->prophesize(ExtraData::class);

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData1->reveal())
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user3->reveal()
            )
            ->shouldNotBeCalled()
        ;
        $calculator = new ConfirmationCalculator($this->extraDataRepository->reveal());
        $result = $calculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = ConfirmationStatus::AT_LEAST_ONE_CONFIRMED;

        $this->assertEquals($expected, $result);
    }

    public function testGetConfirmationStatusForSheetAtLeastOneConfirmedWithFirstNull()
    {
        $extraData2 = $this->prophesize(ExtraData::class);
        $extraData3 = $this->prophesize(ExtraData::class);

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData2->reveal())
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData3->reveal())
        ;
        $calculator = new ConfirmationCalculator($this->extraDataRepository->reveal());
        $result = $calculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = ConfirmationStatus::AT_LEAST_ONE_CONFIRMED;

        $this->assertEquals($expected, $result);
    }

    public function testGetConfirmationStatusForSheetAtLeastOneConfirmedWithLastNull()
    {
        $extraData1 = $this->prophesize(ExtraData::class);
        $extraData2 = $this->prophesize(ExtraData::class);
        $extraData3 = $this->prophesize(ExtraData::class);

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData1->reveal())
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData2->reveal())
        ;
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $calculator = new ConfirmationCalculator($this->extraDataRepository->reveal());
        $result = $calculator->getConfirmationStatusForSheet($this->sheet->reveal());

        $expected = ConfirmationStatus::AT_LEAST_ONE_CONFIRMED;

        $this->assertEquals($expected, $result);
    }
}
