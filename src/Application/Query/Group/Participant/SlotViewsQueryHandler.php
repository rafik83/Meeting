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
     * @return array|SlotView[]
     */
    public function handle(SlotViewsQuery $query)
    {
        $slots = $this->meetingSlotRepository->findByEventAndDay($query->event, $query->day);

        $slotViews = [];

        array_map(function($slot) {
            $slotViews[] = new SlotView($slot);
        }, $slots);

        return $slotViews;
    }
}
