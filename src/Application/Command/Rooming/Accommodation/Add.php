<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Accommodation;

use Proximum\Vimeet\Domain\Model\Event;

class Add extends AbstractAccommodationCommand
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $days = $event->getDays();

        // Add previous day to the default days list of the overnight capacities
        $previousDay = (new \DateTime())
            ->setTimestamp($event->getFirstDay()->getBegin()->getTimestamp())
            ->modify('-1 day')
        ;

        $this->overnightCapacities[] = new AccommodationOvernightCapacityView(
            $previousDay,
            0
        );

        foreach ($days as $day) {
            $this->overnightCapacities[] = new AccommodationOvernightCapacityView(
                $day->getBegin(),
                0
            );
        }
    }
}
