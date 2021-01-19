<?php

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityHandler
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
        $this->userRepository                   = $userRepository;
        $this->participantUnavailableAggregator = $participantUnavailableAggregator;
    }

    /**
     * @param FullUnavailability $fullUnavailability
     */
    public function handle(FullUnavailability $fullUnavailability)
    {
        if ($fullUnavailability->onlyCatalog) {
            $users = $this->userRepository->findByEventAndInCatalog($fullUnavailability->event);
        } else {
            $users = $this->userRepository->findWithEnabledSheetByEvent($fullUnavailability->event);
        }

        foreach ($users as $user) {
            $this->participantUnavailableAggregator->aggregateUnavailability($user, $fullUnavailability->event);
        }
    }
}
