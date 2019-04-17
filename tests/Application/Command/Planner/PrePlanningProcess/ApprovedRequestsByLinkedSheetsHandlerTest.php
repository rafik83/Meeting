<?php

namespace Proximum\Vimeet\Tests\Application\Command\Planner\PrePlanningProcess;

use Prophecy\Prophecy\ObjectProphecy;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\ApprovedRequestsByLinkedSheets;
use Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess\ApprovedRequestsByLinkedSheetsHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
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

    public function testSolutionOptimizeMovingAllowed()
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
}
