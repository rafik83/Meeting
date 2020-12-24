<?php

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityForGivenUsersInEventHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ParticipantUnavailableAggregator */
    private $participantUnavailableAggregator;

    /**
     * @param UserRepositoryInterface          $userRepository
     * @param ParticipantUnavailableAggregator $participantUnavailableAggregator
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantUnavailableAggregator $participantUnavailableAggregator
    ) {
        $this->userRepository = $userRepository;
        $this->participantUnavailableAggregator = $participantUnavailableAggregator;
    }

    /**
     * @param FullUnavailabilityForGivenUsersInEvent $fullUnavailabilityForGivenUsersInEvent
     */
    public function handle(FullUnavailabilityForGivenUsersInEvent $fullUnavailabilityForGivenUsersInEvent)
    {
        $event = $fullUnavailabilityForGivenUsersInEvent->event;
        $users = $this->userRepository->getByIdsIndexedById($fullUnavailabilityForGivenUsersInEvent->userIds);

        foreach ($users as $user) {
            $this->participantUnavailableAggregator->aggregateUnavailability($user, $event);
        }
    }
}
