<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Application\View\Agenda\AgendaSpotView;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class AgendaSpotViewQueryHandler
{
    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var DaySpotViewQueryHandler
     */
    private $daySpotViewQueryHandler;

    /**
     * AgendaSpotViewQueryHandler constructor.
     *
     * @param DayRepositoryInterface  $dayRepository
     * @param DaySpotViewQueryHandler $daySpotViewQueryHandler
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        DaySpotViewQueryHandler $daySpotViewQueryHandler
    ) {
        $this->dayRepository           = $dayRepository;
        $this->daySpotViewQueryHandler = $daySpotViewQueryHandler;
    }

    /**
     * @param AgendaSpotViewQuery $query
     *
     * @return AgendaSpotView
     */
    public function handle(AgendaSpotViewQuery $query)
    {
        $eventDays = $this->dayRepository->findByEvent($query->event);

        $dayViews = [];

        foreach ($eventDays as $dayNumber => $day) {
            $dayViews[] = $this->daySpotViewQueryHandler->handle(
                new DaySpotViewQuery(
                    $day,
                    $dayNumber,
                    $query->event,
                    $query->spot,
                    $query->locale
                )
            );
        }

        return new AgendaSpotView(
            $query->spot->getId(),
            $query->spot->getReference(),
            $dayViews
        );
    }
}
