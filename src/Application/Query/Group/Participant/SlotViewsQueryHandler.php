<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\View\Sheet\Group\Participant\SlotView;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotViewsQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * SlotViewQueryHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param SlotViewsQuery $query
     *
     * @return SlotView[]
     */
    public function handle(SlotViewsQuery $query)
    {
        $slots = $this->meetingSlotRepository->findByEventAndDay($query->day->getEvent(), $query->day);
        $slotViews = [];

        foreach ($slots as $slot) {
            $slotViews[] = new SlotView($slot);
        }

        return $slotViews;
    }
}
