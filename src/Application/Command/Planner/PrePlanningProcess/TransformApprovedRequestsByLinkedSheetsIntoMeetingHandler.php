<?php

namespace Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\TransformRequestIntoMeeting;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class TransformApprovedRequestsByLinkedSheetsIntoMeetingHandler
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

    public function handle(TransformApprovedRequestsByLinkedSheetsIntoMeeting $command): void
    {
        if (ExportSolutionType::SOLUTION_FROM_SCRATCH === $command->solutionType) {
            return;
        }

        $event = $command->event;
        $sheets = $this->getAllSheetsOfLinkedSheetsByEvent($event);

        if (empty($sheets)) {
            return;
        }

        $approvedRequests = $this->requestRepository->findBySheets(
            $event,
            $sheets,
            [Meeting\Request::STATE_APPROVED],
            true
        );

        $countSheetsMetByLinkedSheet = $this->getCountSheetRequestsByLinkedSheet($approvedRequests);

        foreach ($approvedRequests as $request) {
            $fromSheet = $request->getFromSheet();
            $fromLinkedSheets = $fromSheet->getLinkedSheets();

            $toSheet = $request->getToSheet();
            $toLinkedSheets = $toSheet->getLinkedSheets();

            // From sheet
            $isTransformedIntoMeetingRequest = $this->transformRequestIntoMeetingWhenAllLinkedSheetsHasApprovedRequests(
                $request,
                $countSheetsMetByLinkedSheet,
                $fromLinkedSheets,
                $toSheet
            );

            if ($isTransformedIntoMeetingRequest) {
                continue;
            }

            // To sheet
            $this->transformRequestIntoMeetingWhenAllLinkedSheetsHasApprovedRequests(
                $request,
                $countSheetsMetByLinkedSheet,
                $toLinkedSheets,
                $fromSheet
            );
        }
    }

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    private function getAllSheetsOfLinkedSheetsByEvent(Event $event): array
    {
        $someLinkedSheets = $this->linkedSheetsRepository->getByEvent($event);
        $sheets = [];

        foreach ($someLinkedSheets as $linkedSheets) {
            foreach ($linkedSheets->getSheets() as $sheet) {
                $sheets[$sheet->getId()] = $sheet;
            }
        }

        return $sheets;
    }

    /**
     * @param Meeting\Request[] $requests
     *
     * @return array
     */
    private function getCountSheetRequestsByLinkedSheet(array $requests): array
    {
        $countSheetsMetByLinkedSheet = [];

        foreach ($requests as $request) {
            $fromSheet = $request->getFromSheet();
            $fromLinkedSheets = $fromSheet->getLinkedSheets();

            $toSheet = $request->getToSheet();
            $toLinkedSheets = $toSheet->getLinkedSheets();

            $this->incrementSheetsMetByLinkedSheet($countSheetsMetByLinkedSheet, $fromLinkedSheets, $toSheet);
            $this->incrementSheetsMetByLinkedSheet($countSheetsMetByLinkedSheet, $toLinkedSheets, $fromSheet);
        }

        return $countSheetsMetByLinkedSheet;
    }

    private function incrementSheetsMetByLinkedSheet(
        array &$countSheetsMetByLinkedSheet,
        ?LinkedSheets $linkedSheets,
        Sheet $sheetMet
    ): void {
        if (null === $linkedSheets) {
            return;
        }

        $sheetMetId = $sheetMet->getId();
        $linkedSheetsId = $linkedSheets->getId();

        if (!isset($countSheetsMetByLinkedSheet[$linkedSheetsId])) {
            $countSheetsMetByLinkedSheet[$linkedSheetsId] = [];
        }

        if (!isset($countSheetsMetByLinkedSheet[$linkedSheetsId][$sheetMetId])) {
            $countSheetsMetByLinkedSheet[$linkedSheetsId][$sheetMetId] = 0;
        }

        $countSheetsMetByLinkedSheet[$linkedSheetsId][$sheetMetId]++;
    }

    /**
     * Returns true if request is transformed into meeting
     *
     * @param Meeting\Request   $request
     * @param array             $countSheetsMetByLinkedSheet
     * @param LinkedSheets|null $linkedSheets
     * @param Sheet             $sheetMet
     *
     * @return bool
     */
    private function transformRequestIntoMeetingWhenAllLinkedSheetsHasApprovedRequests(
        Meeting\Request $request,
        array &$countSheetsMetByLinkedSheet,
        ?LinkedSheets $linkedSheets,
        Sheet $sheetMet
    ): bool {
        if (null === $linkedSheets) {
            return false;
        }

        $sheetMetId = $sheetMet->getId();
        $linkedSheetsId = $linkedSheets->getId();

        if (!isset($countSheetsMetByLinkedSheet[$linkedSheetsId][$sheetMetId])) {
            return false;
        }

        $countApprovedRequestWithThisSheet = $countSheetsMetByLinkedSheet[$linkedSheetsId][$sheetMetId];
        $allLinkedSheetsHasApprovedRequestsWithThisSheet = $countApprovedRequestWithThisSheet === $linkedSheets->countSheets();

        if (!$allLinkedSheetsHasApprovedRequestsWithThisSheet) {
            return false;
        }

        $this->commandBus->handle(new TransformRequestIntoMeeting($request, Meeting::CREATED_BY_PLANNER, true, false));
        unset($countSheetsMetByLinkedSheet[$linkedSheetsId][$sheetMetId]);

        return true;
    }
}
