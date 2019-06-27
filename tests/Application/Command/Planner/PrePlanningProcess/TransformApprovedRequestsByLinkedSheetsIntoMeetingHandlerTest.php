<?php

namespace Proximum\Vimeet\Tests\Application\Command\Planner\PrePlanningProcess;

use Prophecy\Prophecy\ObjectProphecy;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\TransformApprovedRequestsByLinkedSheetsIntoMeeting;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\TransformApprovedRequestsByLinkedSheetsIntoMeetingHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class TransformApprovedRequestsByLinkedSheetsIntoMeetingHandlerTest extends TestCase
{
    /** @var ObjectProphecy|Event */
    private $event;

    /** @var TransformApprovedRequestsByLinkedSheetsIntoMeetingHandler */
    private $transformIntoMeetingApprovedRequestsByLinkedSheetsHandler;

    /** @var ObjectProphecy|CommandBusInterface */
    private $commandBus;

    /** @var ObjectProphecy|LinkedSheetsRepositoryInterface */
    private $linkedSheetsRepository;

    /** @var ObjectProphecy|RequestRepositoryInterface */
    private $requestRepository;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $this->transformIntoMeetingApprovedRequestsByLinkedSheetsHandler = new TransformApprovedRequestsByLinkedSheetsIntoMeetingHandler(
            $this->commandBus->reveal(),
            $this->linkedSheetsRepository->reveal(),
            $this->requestRepository->reveal()
        );
    }

    public function testSolutionFromScratch()
    {
        $this
            ->linkedSheetsRepository
            ->getByEvent($this->event->reveal())
            ->shouldNotBeCalled()
        ;

        $this
            ->requestRepository
            ->getRequestsOfSheetsWithSheets()
            ->shouldNotBeCalled()
        ;

        $this->commandBus->handle()->shouldNotBeCalled();

        $this->transformIntoMeetingApprovedRequestsByLinkedSheetsHandler->handle(
            new TransformApprovedRequestsByLinkedSheetsIntoMeeting(
                $this->event->reveal(),
                ExportSolutionType::SOLUTION_FROM_SCRATCH
            )
        );
    }

    public function testSolutionOptimizeMovingAllowedAndNoLinkedSheets()
    {
        $this
            ->linkedSheetsRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->requestRepository
            ->getRequestsOfSheetsWithSheets()
            ->shouldNotBeCalled()
        ;

        $this->commandBus->handle()->shouldNotBeCalled();

        $this->transformIntoMeetingApprovedRequestsByLinkedSheetsHandler->handle(
            new TransformApprovedRequestsByLinkedSheetsIntoMeeting(
                $this->event->reveal(),
                ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED
            )
        );
    }

    public function testSolutionOptimizeMovingAllowed()
    {
        $sheetMet = $this->prophesize(Sheet::class);
        $sheetMet->getId()->shouldBeCalled()->willReturn(333);
        $sheetMet->getLinkedSheets()->shouldBeCalled()->willReturn(null);

        $otherSheet = $this->prophesize(Sheet::class);
        $otherSheet->getId()->shouldBeCalled()->willReturn(1984);
        $otherSheet->getLinkedSheets()->shouldBeCalled()->willReturn(null);

        $linkedSheets1Sheet1 = $this->prophesize(Sheet::class);
        $linkedSheets1Sheet1->getId()->shouldBeCalled()->willReturn(11);

        $linkedSheets1Sheet2 = $this->prophesize(Sheet::class);
        $linkedSheets1Sheet2->getId()->shouldBeCalled()->willReturn(12);

        $linkedSheets1 = $this->prophesize(LinkedSheets::class);
        $linkedSheets1->getId()->shouldBeCalled()->willReturn(111);
        $linkedSheets1->countSheets()->shouldBeCalled()->willReturn(2);
        $linkedSheets1
            ->getSheets()
            ->shouldBeCalled()
            ->willReturn([$linkedSheets1Sheet1->reveal(), $linkedSheets1Sheet2->reveal()])
        ;
        $linkedSheets1Sheet1->getLinkedSheets()->shouldBeCalled()->willReturn($linkedSheets1->reveal());
        $linkedSheets1Sheet2->getLinkedSheets()->shouldBeCalled()->willReturn($linkedSheets1->reveal());

        $linkedSheets2Sheet1 = $this->prophesize(Sheet::class);
        $linkedSheets2Sheet1->getId()->shouldBeCalled()->willReturn(21);

        $linkedSheets2Sheet2 = $this->prophesize(Sheet::class);
        $linkedSheets2Sheet2->getId()->shouldBeCalled()->willReturn(22);

        $linkedSheets2 = $this->prophesize(LinkedSheets::class);
        $linkedSheets2->getId()->shouldBeCalled()->willReturn(222);
        $linkedSheets2->countSheets()->shouldBeCalled()->willReturn(2);
        $linkedSheets2
            ->getSheets()
            ->shouldBeCalled()
            ->willReturn([$linkedSheets2Sheet1->reveal(), $linkedSheets2Sheet2->reveal()])
        ;
        $linkedSheets2Sheet1->getLinkedSheets()->shouldBeCalled()->willReturn($linkedSheets2->reveal());
        $linkedSheets2Sheet2->getLinkedSheets()->shouldBeCalled()->willReturn($linkedSheets2->reveal());

        $this
            ->linkedSheetsRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$linkedSheets1->reveal(), $linkedSheets2->reveal()])
        ;

        $request1 = $this->prophesize(Request::class);
        $request1->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $request1->getFromSheet()->shouldBeCalled()->willReturn($linkedSheets1Sheet1->reveal());
        $request1->getToSheet()->shouldBeCalled()->willReturn($sheetMet->reveal());

        $request2 = $this->prophesize(Request::class);
        $request2->getFromSheet()->shouldBeCalled()->willReturn($sheetMet->reveal());
        $request2->getToSheet()->shouldBeCalled()->willReturn($linkedSheets1Sheet2->reveal());

        $request3 = $this->prophesize(Request::class);
        $request3->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $request3->getFromSheet()->shouldBeCalled()->willReturn($linkedSheets1Sheet1->reveal());
        $request3->getToSheet()->shouldBeCalled()->willReturn($linkedSheets2Sheet1->reveal());

        $request4 = $this->prophesize(Request::class);
        $request4->getFromSheet()->shouldBeCalled()->willReturn($linkedSheets2Sheet1->reveal());
        $request4->getToSheet()->shouldBeCalled()->willReturn($linkedSheets1Sheet2->reveal());

        $request5 = $this->prophesize(Request::class);
        $request5->getFromSheet()->shouldBeCalled()->willReturn($otherSheet->reveal());
        $request5->getToSheet()->shouldBeCalled()->willReturn($linkedSheets2Sheet2->reveal());

        $sheets = [
            11 => $linkedSheets1Sheet1->reveal(),
            12 => $linkedSheets1Sheet2->reveal(),
            21 => $linkedSheets2Sheet1->reveal(),
            22 => $linkedSheets2Sheet2->reveal(),
        ];

        $this
            ->requestRepository
            ->findBySheets(
                $this->event->reveal(),
                $sheets,
                [Request::STATE_APPROVED],
                true
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $request1->reveal(),
                    $request2->reveal(),
                    $request3->reveal(),
                    $request4->reveal(),
                    $request5->reveal(),
                ]
            )
        ;

        $this
            ->commandBus
            ->handle(new TransformRequestIntoMeeting($request1->reveal(), Meeting::CREATED_BY_PLANNER, true, false))
            ->shouldBeCalled()
        ;
        $this
            ->commandBus
            ->handle(new TransformRequestIntoMeeting($request3->reveal(), Meeting::CREATED_BY_PLANNER, true, false))
            ->shouldBeCalled()
        ;

        $this->transformIntoMeetingApprovedRequestsByLinkedSheetsHandler->handle(
            new TransformApprovedRequestsByLinkedSheetsIntoMeeting(
                $this->event->reveal(),
                ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED
            )
        );
    }
}
