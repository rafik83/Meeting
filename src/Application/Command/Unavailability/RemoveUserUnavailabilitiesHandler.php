<?php

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class RemoveUserUnavailabilitiesHandler
{
    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;
    
    public function __construct(UnavailabilityRepositoryInterface $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    public function handle(RemoveUserUnavailabilities $remove): void
    {
        $unavailabilities = $this->unavailabilityRepository
            ->findByUserAndEventCreatedByUser($remove->user, $remove->event);

        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilityRepository->remove($unavailability);
        }
    }
}
