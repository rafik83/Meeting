<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Application\View\Agenda\Slot\SpotMeetingSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\SpotSlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var SpotUnavailabilityRepositoryInterface
     */
    private $spotUnavailabilityRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var MeetingSlotViewQueryHandler
     */
    private $meetingSlotViewQueryHandler;

    /**
     * SlotViewQueryHandler constructor.
     *
     * @param MeetingRepositoryInterface            $meetingRepository
     * @param MeetingSlotRepositoryInterface        $meetingSlotRepository
     * @param SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository
     * @param MeetingSlotViewQueryHandler           $meetingSlotViewQueryHandler
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository,
        MeetingSlotViewQueryHandler $meetingSlotViewQueryHandler
    ) {
        $this->meetingSlotRepository        = $meetingSlotRepository;
        $this->spotUnavailabilityRepository = $spotUnavailabilityRepository;
        $this->meetingRepository            = $meetingRepository;
        $this->meetingSlotViewQueryHandler  = $meetingSlotViewQueryHandler;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @return SpotSlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $spotUnavailabilities = $this->spotUnavailabilityRepository->findBySpot($query->spot);

        $slots     = $this->meetingSlotRepository->findByEventAndDay($query->event, $query->day);
        $meetings  = $this->getMeetingsBySlotId($query->spot);
        $slotViews = [];

        foreach ($slots as $slot) {
            $isUnavailable = $this->hasUnavailability($slot, $spotUnavailabilities);

            if ($isUnavailable) {
                $type = SlotAvailability::UNAVAILABILITY;
            } elseif (!empty($meetings[$slot->getId()])) {
                $type = SlotAvailability::MEETING_UNAVAILABILITY;
            } else {
                $type = SlotAvailability::SLOT_AVAILABLE;
            }

            $slotView = new SpotSlotView(
                $slot,
                $type,
                isset($meetings[$slot->getId()]) ?
                    $this->buildMeetingView($meetings[$slot->getId()], $query->locale) :
                    []
            );

            $slotViews[] = $slotView;
        }

        return $slotViews;
    }

    /**
     * @param Spot $spot
     *
     * @return array of Meeting[] indexed by Slot id
     */
    private function getMeetingsBySlotId(Spot $spot)
    {
        $meetings = $this->meetingRepository->findBySpotWithSheets($spot);

        $meetingsIndexBySlotIds = [];

        /** @var Meeting $meeting */
        foreach ($meetings as $meeting) {
            $meetingsIndexBySlotIds[$meeting->getSlot()->getId()][] = $meeting;
        }

        return $meetingsIndexBySlotIds;
    }

    /**
     * @param MeetingSlot          $slot
     * @param SpotUnavailability[] $spotUnavailabilities
     *
     * @return bool
     */
    private function hasUnavailability(MeetingSlot $slot, array $spotUnavailabilities)
    {
        /** @var SpotUnavailability $spotUnavailability */
        foreach ($spotUnavailabilities as $spotUnavailability) {
            if ($spotUnavailability->getSlot()->getId() === $slot->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Meeting[] $meetings
     * @param string    $locale
     *
     * @return SpotMeetingSlotView[]
     */
    private function buildMeetingView(array $meetings, $locale)
    {
        $meetingViews = [];

        /** @var Meeting $meeting */
        foreach ($meetings as $meeting) {
            $meetingViews[] = $this->meetingSlotViewQueryHandler->handle(
                new MeetingSlotViewQuery($meeting, $locale)
            );
        }

        return $meetingViews;
    }
}
