<?php

namespace Proximum\Vimeet\Tests\Application\Command\Planner\PrePlanningProcess;

use Prophecy\Prophecy\ObjectProphecy;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\ApprovedRequestsByLinkedSheets;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\ApprovedRequestsByLinkedSheetsHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class ApprovedRequestsByLinkedSheetsHandlerTest extends TestCase
{
    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ApprovedRequestsByLinkedSheetsHandler */
    private $approvedRequestsByLinkedSheetsHandler;

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

        $this->approvedRequestsByLinkedSheetsHandler = new ApprovedRequestsByLinkedSheetsHandler(
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

        $this->approvedRequestsByLinkedSheetsHandler->handle(
            new ApprovedRequestsByLinkedSheets($this->event->reveal(), ExportSolutionType::SOLUTION_FROM_SCRATCH)
        );
    }

    public function testSolutionOptimizeLocked()
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

        $this->approvedRequestsByLinkedSheetsHandler->handle(
            new ApprovedRequestsByLinkedSheets($this->event->reveal(), ExportSolutionType::SOLUTION_OPTIMIZE_LOCKED)
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

        $this->approvedRequestsByLinkedSheetsHandler->handle(
            new ApprovedRequestsByLinkedSheets(
                $this->event->reveal(),
                ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED
            )
        );
    }

    public function testSolutionOptimizeMovingAllowed()
    {
        $linkedSheets1Sheet1 = $this->prophesize(Sheet::class);
        $linkedSheets1Sheet1->getId()->shouldBeCalled()->willReturn(11);

        $linkedSheets1Sheet2 = $this->prophesize(Sheet::class);
        $linkedSheets1Sheet2->getId()->shouldBeCalled()->willReturn(12);

        $linkedSheets1 = $this->prophesize(LinkedSheets::class);
        $linkedSheets1
            ->getSheets()
            ->shouldBeCalled()
            ->willReturn([$linkedSheets1Sheet1->reveal(), $linkedSheets1Sheet2->reveal()])
        ;

        $linkedSheets2Sheet1 = $this->prophesize(Sheet::class);
        $linkedSheets2Sheet1->getId()->shouldBeCalled()->willReturn(21);

        $linkedSheets2Sheet2 = $this->prophesize(Sheet::class);
        $linkedSheets2Sheet2->getId()->shouldBeCalled()->willReturn(22);

        $linkedSheets2 = $this->prophesize(LinkedSheets::class);
        $linkedSheets2
            ->getSheets()
            ->shouldBeCalled()
            ->willReturn([$linkedSheets2Sheet1->reveal(), $linkedSheets2Sheet2->reveal()])
        ;

        $this
            ->linkedSheetsRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$linkedSheets1->reveal(), $linkedSheets2->reveal()])
        ;

        $request1 = $this->prophesize(Request::class);
        $request2 = $this->prophesize(Request::class);

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
            ->willReturn([$request1->reveal(), $request2->reveal()])
        ;

        $this->commandBus->handle()->shouldNotBeCalled();

        $this->approvedRequestsByLinkedSheetsHandler->handle(
            new ApprovedRequestsByLinkedSheets(
                $this->event->reveal(),
                ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED
            )
        );
    }
}
