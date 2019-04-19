<?php

namespace Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class ApprovedRequestsByLinkedSheetsHandler
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var LinkedSheetsRepositoryInterface */
    private $linkedSheetsRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        LinkedSheetsRepositoryInterface $linkedSheetsRepository,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->commandBus = $commandBus;
        $this->linkedSheetsRepository = $linkedSheetsRepository;
        $this->requestRepository = $requestRepository;
    }

    public function handle(ApprovedRequestsByLinkedSheets $approvedRequestsByLinkedSheets): void
    {
        if (ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED !== $approvedRequestsByLinkedSheets->solutionType) {
            return;
        }

        $someLinkedSheets = $this->linkedSheetsRepository->getByEvent($approvedRequestsByLinkedSheets->event);
        $sheets = [];

        foreach ($someLinkedSheets as $linkedSheets) {
            foreach ($linkedSheets->getSheets() as $sheet) {
                $sheets[$sheet->getId()] = $sheet;
            }
        }

        if (empty($sheets)) {
            return;
        }

        // 2. get all accepted requests not transformed into meeting of all linked sheets
        $approvedRequests = $this->requestRepository->findBySheets(
            $approvedRequestsByLinkedSheets->event,
            $sheets,
            [Meeting\Request::STATE_APPROVED],
            true
        );

        // 3. for each request, if there is a accepted request of its linked sheets
        // 3.1. transform a request into meeting
        //$this->commandBus->handle(new TransformRequestIntoMeeting($request, Meeting::CREATED_BY_PLANNER));

        // 3.2. ignore not transformed requests
        // 4. dispatch events ?
    }
}
