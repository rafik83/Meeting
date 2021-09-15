<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\AgendaDayView;

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
                $query->sheet->getEvent(),
                $query->day,
                $query->sheet,
                $query->participant,
                $query->happenings,
                $query->unavailabilities,
                $query->masses,
                $query->meetings,
                $query->massAssignments,
                $query->meetingOtherSheets
            )
        );

        return new AgendaDayView($query->dayNumber, $slotViews);
    }
}
