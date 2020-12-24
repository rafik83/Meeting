<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Application\View\Sheet\Details\SheetMeetingIndicatorView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class SheetMeetingIndicatorQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * SheetMeetingIndicatorQueryHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MeetingRepositoryInterface $meetingRepository
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param SheetMeetingIndicatorQuery $query
     *
     * @return SheetMeetingIndicatorView
     */
    public function handle(SheetMeetingIndicatorQuery $query): SheetMeetingIndicatorView
    {
        return new SheetMeetingIndicatorView(
            $this->requestRepository->countApprovedRequestSentBySheet($query->sheet),
            $this->requestRepository->countPendingRequestSentBySheet($query->sheet),
            $this->requestRepository->countRefusedRequestSentBySheet($query->sheet),
            $this->requestRepository->countApprovedPropositionReceivedBySheet($query->sheet),
            $this->requestRepository->countPendingPropositionReceivedBySheet($query->sheet, false),
            $this->requestRepository->countRefusedPropositionReceivedBySheet($query->sheet),
            $this->meetingRepository->countMeetingsOfSheet($query->sheet)
        );
    }
}
