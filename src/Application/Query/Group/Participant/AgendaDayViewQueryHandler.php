<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;

class AgendaDayViewQueryHandler
{
    /**
     * @var SlotViewsQueryHandler
     */
    private $slotViewsQueryHandler;

    /**
     * AgendaDayViewQueryHandler constructor.
     *
     * @param SlotViewsQueryHandler $slotViewsQueryHandler
     */
    public function __construct(SlotViewsQueryHandler $slotViewsQueryHandler)
    {
        $this->slotViewsQueryHandler = $slotViewsQueryHandler;
    }

    /**
     * @param AgendaDayViewQuery $query
     *
     * @return AgendaDayView
     */
    public function handle(AgendaDayViewQuery $query)
    {
        $slotViews = $this->slotViewsQueryHandler->handle(
            new SlotViewsQuery($query->day)
        );

        return new AgendaDayView($slotViews);
    }
}
