<?php

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\RemoveUnavailabilityEvent;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotDeleteUnavailabilityException;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RemoveHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * RemoveHandler constructor.
     *
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     * @param DelayedEventDispatcher            $eventDispatcher
     */
    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->eventDispatcher          = $eventDispatcher;
    }

    /**
     * @param Remove $remove
     *
     * @throws CanNotDeleteUnavailabilityException
     */
    public function handle(Remove $remove)
    {
        $user = $remove->unavailability->getUser();
        $event = $remove->unavailability->getEvent();

        if (!$remove->unavailability->isCreatedByUser()) {
            throw new CanNotDeleteUnavailabilityException(
                'The unavailability can not be removed by the user, because it is not created by the user'
            );
        }

        $this->unavailabilityRepository->remove($remove->unavailability);

        $this->eventDispatcher->dispatch(
            Events::UNAVAILABILITY_REMOVED,
            new RemoveUnavailabilityEvent($user, $event)
        );
    }
}
