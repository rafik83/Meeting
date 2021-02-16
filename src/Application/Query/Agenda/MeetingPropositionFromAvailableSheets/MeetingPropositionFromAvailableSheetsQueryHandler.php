<?php

namespace Proximum\Vimeet\Application\Query\Agenda\MeetingPropositionFromAvailableSheets;

use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingPropositionFromAvailableSheetsQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /**
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @deprecated This code is not used anymore.
     *
     * @param MeetingPropositionFromAvailableSheetsQuery $meetingPropositionFromAvailableSheetsQuery
     *
     * @return int
     */
    public function handle(MeetingPropositionFromAvailableSheetsQuery $meetingPropositionFromAvailableSheetsQuery): int
    {
        return $this->requestRepository->countPendingPropositionReceivedBySheetWithAvailableFromSheet(
            $meetingPropositionFromAvailableSheetsQuery->sheet,
            [$meetingPropositionFromAvailableSheetsQuery->meetingSlot->getId()]
        );
    }
}
