<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class DayManager
{
    /** @var DayRepositoryInterface */
    private $dayRepository;

    /**
     * @param DayRepositoryInterface $dayRepository
     */
    public function __construct(DayRepositoryInterface $dayRepository)
    {
        $this->dayRepository = $dayRepository;
    }

    /**
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     *
     * @return Event\Day
     */
    public function create(Event $event, \DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $day = new Event\Day($event, $begin, $end);

        $this->dayRepository->add($day);
        $event->addDay($day);

        return $day;
    }
}
