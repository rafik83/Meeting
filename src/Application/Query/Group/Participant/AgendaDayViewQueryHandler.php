<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;

class AgendaDayViewQueryHandler
{
    /**
     * @var SlotViewQueryHandler
     */
    private $slotViewQueryHandler;

    /**
     * AgendaDayViewQueryHandler constructor.
     *
     * @param SlotViewQueryHandler $slotViewQueryHandler
     */
    public function __construct(SlotViewQueryHandler $slotViewQueryHandler)
    {
        $this->slotViewQueryHandler = $slotViewQueryHandler;
    }

    /**
     * @param AgendaDayViewQuery $query
     *
     * @return AgendaDayView
     */
    public function handle(AgendaDayViewQuery $query)
    {
        $slotViews = $this->slotViewQueryHandler->handle(
            new SlotViewQuery(
                $query->event,
                $query->day
            )
        );

        return new AgendaDayView($query->dayNumber, $slotViews);
    }
}
