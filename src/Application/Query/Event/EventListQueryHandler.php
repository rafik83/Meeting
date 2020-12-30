<?php

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Application\View\Event\DayView;
use Proximum\Vimeet\Application\View\Event\EventListsView;
use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\View\EventListView;

class EventListQueryHandler
{
    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * EventListQueryHandler constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        \DateTimeInterface $datetime
    ) {
        $this->datetime        = $datetime;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param EventListQuery $query
     *
     * @return EventListsView
     */
    public function handle(EventListQuery $query)
    {
        $eventListView = [];
        $pastEventDate = clone $this->datetime->modify('-1 month');

        if (EventListQuery::STATE_ARCHIVED === $query->state) {
            $events = $this->eventRepository->findArchivedByAdmin($query->admin);
        } else {
            $events = $this->eventRepository->getEventsWithDaysByAdmin($query->admin);
        }

        foreach ($events as $event) {
            $eventView = new EventListView(
                $event->getId(),
                $event->getTitle(),
                $event->getDomain(),
                $event->getLocales(),
                $event->getFallback(),
                $event->isVisible(),
                array_map(function (Day $day) {
                    return new DayView($day->getStartTime(), $day->getEndTime());
                }, $event->getDays()),
                $event->getConfiguration()->isVisio(),
                $event->isAccessControlEnabled()
            );

            if (EventListQuery::STATE_ARCHIVED !== $query->state) {
                try {
                    $lastDay = $event->getLastDay();

                    if ($lastDay->getDay() < $pastEventDate && EventListQuery::STATE_CURRENT === $query->state) {
                        continue;
                    }

                    if ($lastDay->getDay() > $pastEventDate && EventListQuery::STATE_PAST === $query->state) {
                        continue;
                    }
                } catch (DayNotDefinedException $exception) {
                    if (EventListQuery::STATE_PAST === $query->state) {
                        continue;
                    }
                }
            }

            $eventListView[] = $eventView;
        }

        return new EventListsView($eventListView);
    }
}
