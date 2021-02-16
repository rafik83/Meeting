<?php

namespace Proximum\Vimeet\Application\Query\Schedule;

use Proximum\Vimeet\Application\View\Schedule\SlotView;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * SlotQueryHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @return SlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $slotViews = [];

        $meetingSlotWithMeetingIds = $this->meetingSlotRepository->findWithAtLeastOneMeetingByEvent($query->event);

        foreach ($this->meetingSlotRepository->findByEvent($query->event) as $slot) {
            $slotViews[] = new SlotView(
                $slot->getBegin(),
                $slot->getEnd(),
                $slot->getId(),
                $slot->duration(),
                $slot->isLocked(),
                isset($meetingSlotWithMeetingIds[$slot->getId()])
            );
        }

        return $slotViews;
    }
}
