<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class SpotFillingRateQueryHandler
{
    /** @var array */
    private $spotUnavailabilities = [];

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param SpotFillingRateQuery $query
     *
     * @return SpotFillingRateDayView[]
     */
    public function handle(SpotFillingRateQuery $query): array
    {
        /** @var SpotFillingRateDayView[] $days */
        $days = [];

        foreach ($query->meetingSlots as $meetingSlot) {
            $spotsOnSlot = [];
            $capacity = 0;

            foreach ($query->spots as $spot) {
                if ($this->isSpotAvailableForThisSlot($spot, $meetingSlot)) {
                    $spotsOnSlot[] = $spot;
                    $capacity += $spot->getMeetingCapacity();
                }
            }

            $numberOfMeeting = $this->meetingRepository->countMeetingForSpotsAndSlot($spotsOnSlot, $meetingSlot);

            // Avoid dividing by 0
            if (0 === $capacity) {
                $capacity = 1;
            }

            $fillingRate = 100 * ($numberOfMeeting / $capacity);

            $spotFillingRateSlotView = new SpotFillingRateSlotView(
                $meetingSlot->getBegin(),
                $meetingSlot->getEnd(),
                $fillingRate
            );

            if (!isset($days[$meetingSlot->getBegin()->format('Y-m-d')])) {
                $days[$meetingSlot->getBegin()->format('Y-m-d')] = new SpotFillingRateDayView(
                    $meetingSlot->getBegin(),
                    $query->event->getTimeZone()
                );
            }

            $days[$meetingSlot->getBegin()->format('Y-m-d')]->addSlotFillingRate($spotFillingRateSlotView);
        }

        return $days;
    }

    /**
     * @param Spot        $spot
     * @param MeetingSlot $slot
     *
     * @return int
     */
    private function isSpotAvailableForThisSlot(Spot $spot, MeetingSlot $slot): int
    {
        if (!isset($this->spotUnavailabilities[$spot->getId()])) {
            $this->spotUnavailabilities[$spot->getId()] = [];

            foreach ($spot->getSpotUnavailabilities() as $unavailability) {
                $this->spotUnavailabilities[$spot->getId()][$unavailability->getSlot()->getId()] = true;
            }
        }

        return !isset($this->spotUnavailabilities[$spot->getId()][$slot->getId()]);
    }
}
