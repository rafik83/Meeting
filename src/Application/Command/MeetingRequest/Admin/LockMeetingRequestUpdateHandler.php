<?php

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class LockMeetingRequestUpdateHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param LockMeetingRequestUpdate $lockMeetingRequestUpdate
     */
    public function handle(LockMeetingRequestUpdate $lockMeetingRequestUpdate)
    {
        $lockMeetingRequestUpdate
            ->event
            ->getConfiguration()
            ->setMeetingRequestUpdateLocked($lockMeetingRequestUpdate->lock)
        ;

        $this->eventRepository->set($lockMeetingRequestUpdate->event);
    }
}
