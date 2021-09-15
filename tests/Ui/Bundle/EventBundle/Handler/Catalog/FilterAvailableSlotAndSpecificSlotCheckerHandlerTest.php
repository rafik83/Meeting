<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotCheckerView;

class FilterAvailableSlotAndSpecificSlotCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $dDayGuesser;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $meetingSlotRepository;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->dDayGuesser = $this->prophesize(DDayGuesser::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
    }

    public function testHandleNotDDay()
    {
        $handleQuery = new FilterAvailableSlotAndSpecificSlotChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            null
        );

        $this->dDayGuesser->isItDDayAndFeatureEnabled($this->event->reveal())->shouldBeCalled()->willReturn(false);
        $this->sheet->hasUserParticipant($this->user->reveal())->willReturn(true);

        $handler = new FilterAvailableSlotAndSpecificSlotCheckerHandler(
            $this->dDayGuesser->reveal(),
            $this->queryBus->reveal(),
            $this->meetingSlotRepository->reveal()
        );

        $result = $handler->handle($handleQuery);
        $expected = new FilterAvailableSlotAndSpecificSlotCheckerView(
            false,
            null
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleNoSlotId()
    {
        $handleQuery = new FilterAvailableSlotAndSpecificSlotChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            null
        );

        $participant = $this->prophesize(Participant::class);
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($participant->reveal());
        $this->dDayGuesser->isItDDayAndFeatureEnabled($this->event->reveal())->shouldBeCalled()->willReturn(true);
        $this->sheet->hasUserParticipant($this->user->reveal())->willReturn(true);

        $availableSlot = $this->prophesize(AvailableSlotView::class);
        $this->queryBus
            ->handle(
                new AvailableSlotsByParticipantQuery(
                    $this->event->reveal(),
                    $participant->reveal()
                )
            )->shouldBeCalled()
            ->willReturn([$availableSlot->reveal()])
        ;

        $handler = new FilterAvailableSlotAndSpecificSlotCheckerHandler(
            $this->dDayGuesser->reveal(),
            $this->queryBus->reveal(),
            $this->meetingSlotRepository->reveal()
        );

        $result = $handler->handle($handleQuery);
        $expected = new FilterAvailableSlotAndSpecificSlotCheckerView(
            true,
            null
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleNoSlotIdPresentInAvailableSlot()
    {
        $handleQuery = new FilterAvailableSlotAndSpecificSlotChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            12
        );

        $participant = $this->prophesize(Participant::class);
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($participant->reveal());
        $this->dDayGuesser->isItDDayAndFeatureEnabled($this->event->reveal())->shouldBeCalled()->willReturn(true);
        $this->sheet->hasUserParticipant($this->user->reveal())->willReturn(true);

        $dateTime = new \DateTime();
        $availableSlot = new AvailableSlotView(1, $dateTime, $dateTime);
        $this->queryBus
            ->handle(
                new AvailableSlotsByParticipantQuery(
                    $this->event->reveal(),
                    $participant->reveal()
                )
            )->shouldBeCalled()
            ->willReturn([$availableSlot])
        ;

        $slot = $this->prophesize(MeetingSlot::class);
        $this->meetingSlotRepository->findById(12)->shouldBeCalled()->willReturn($slot->reveal());
        $slot->getId()->willReturn(12);

        $handler = new FilterAvailableSlotAndSpecificSlotCheckerHandler(
            $this->dDayGuesser->reveal(),
            $this->queryBus->reveal(),
            $this->meetingSlotRepository->reveal()
        );

        $result = $handler->handle($handleQuery);
        $expected = new FilterAvailableSlotAndSpecificSlotCheckerView(
            true,
            null
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $handleQuery = new FilterAvailableSlotAndSpecificSlotChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            12
        );

        $participant = $this->prophesize(Participant::class);
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($participant->reveal());
        $this->dDayGuesser->isItDDayAndFeatureEnabled($this->event->reveal())->shouldBeCalled()->willReturn(true);
        $this->sheet->hasUserParticipant($this->user->reveal())->willReturn(true);

        $dateTime = new \DateTime();
        $availableSlot = new AvailableSlotView(12, $dateTime, $dateTime);
        $this->queryBus
            ->handle(
                new AvailableSlotsByParticipantQuery(
                    $this->event->reveal(),
                    $participant->reveal()
                )
            )->shouldBeCalled()
            ->willReturn([$availableSlot])
        ;

        $slot = $this->prophesize(MeetingSlot::class);
        $this->meetingSlotRepository->findById(12)->shouldBeCalled()->willReturn($slot->reveal());
        $slot->getId()->willReturn(12);

        $handler = new FilterAvailableSlotAndSpecificSlotCheckerHandler(
            $this->dDayGuesser->reveal(),
            $this->queryBus->reveal(),
            $this->meetingSlotRepository->reveal()
        );

        $result = $handler->handle($handleQuery);
        $expected = new FilterAvailableSlotAndSpecificSlotCheckerView(
            true,
            $slot->reveal()
        );

        $this->assertEquals($expected, $result);
    }
}
