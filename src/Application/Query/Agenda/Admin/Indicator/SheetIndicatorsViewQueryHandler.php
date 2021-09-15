<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator;

use Proximum\Vimeet\Application\View\Agenda\Admin\Indicator\SheetIndicatorsView;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class SheetIndicatorsViewQueryHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var IndicatorCalculator
     */
    private $indicatorCalculator;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param IndicatorCalculator        $indicatorCalculator
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        IndicatorCalculator $indicatorCalculator
    ) {
        $this->meetingRepository   = $meetingRepository;
        $this->requestRepository   = $requestRepository;
        $this->indicatorCalculator = $indicatorCalculator;
    }

    /**
     * @param SheetIndicatorsViewQuery $query
     *
     * @return SheetIndicatorsView
     */
    public function handle(SheetIndicatorsViewQuery $query)
    {
        $sheet = $query->sheet;

        // Count the request per sheet
        $request = $this->requestRepository->countRequestSentBySheet($sheet);

        // Count the proposition per sheet
        $propositions = $this->requestRepository->countPropositionReceivedBySheet($sheet);

        $indicator            = $this->indicatorCalculator->getIndicator($sheet);
        $meetingRequestsCount = $indicator->meetingRequestsCount;
        $slotCount            = $indicator->slotCount;
        $usableSlots          = $indicator->usableSlots;
        $pendingProposition   = $indicator->pendingPropositionCount;
        $placedMeetingsNumber = $this->meetingRepository->countMeetingsOfSheet($sheet);

        return new SheetIndicatorsView(
            $request,
            $propositions,
            $meetingRequestsCount,
            $slotCount,
            $usableSlots,
            $placedMeetingsNumber,
            $pendingProposition
        );
    }
}
