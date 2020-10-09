<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Application\Exception\Happening\HappeningNotFoundException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingNotFoundException;
use Proximum\Vimeet\Domain\Exception\Event\EventNotFoundException;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class GuessChatMessageLinkableObjectHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        HappeningRepositoryInterface $happeningRepository,
        EventRepositoryInterface $eventRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->happeningRepository = $happeningRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @throws HappeningNotFoundException
     * @throws MeetingNotFoundException
     * @throws EventNotFoundException
     */
    public function handle(GuessChatMessageLinkableObject $query): ChatMessageLinkableInterface
    {
        if ('happening' === $query->objectType) {
            $happening = $this->happeningRepository->getById($query->objectId);

            if (null === $happening) {
                throw new HappeningNotFoundException('Happening not found for given id.');
            }

            return $happening;
        }

        if ('meeting' === $query->objectType) {
            $meeting = $this->meetingRepository->findById($query->objectId);

            if (null === $meeting) {
                throw new MeetingNotFoundException('Meeting not found for given id.');
            }

            return $meeting;
        }

        if ('networking' === $query->objectType) {
            $event = $this->eventRepository->getById($query->objectId);

            if (null === $event) {
                throw new EventNotFoundException('Event not found for given id.');
            }

            return $event;
        }

        throw new \InvalidArgumentException('Invalid ObjectType.');
    }
}
