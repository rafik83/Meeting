<?php

namespace Proximum\Vimeet\Application\Query\Schedule;

use Proximum\Vimeet\Application\View\Schedule\SlotView;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * SlotQueryHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param MeetingRepositoryInterface     $meetingRepository
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @return SlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $slotViews = [];

        $meetingSlotIds = $this->formatMeetingSlotIds(
            $this->meetingSlotRepository->findWithAtLeastOneMeetingByEvent($query->event)
        );

        foreach ($this->meetingSlotRepository->findByEvent($query->event) as $slot) {
            $slotViews[] = new SlotView(
                $slot->getBegin(),
                $slot->getEnd(),
                $slot->duration(),
                $slot->getId(),
                $slot->isLocked(),
                in_array($slot->getId(), $meetingSlotIds)
            );
        }

        return $slotViews;
    }

    /**
     * @param array $meetingSlotIds
     *
     * @return array
     */
    public function formatMeetingSlotIds(array $meetingSlotIds)
    {
        $meetingSlotIdsFormatted = [];

        foreach ($meetingSlotIds as $meetingSlotId) {
            $meetingSlotIdsFormatted[] = $meetingSlotId['id'];
        }

        return $meetingSlotIdsFormatted;
    }
}
