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
            ->findByUserEventAndSheet($remove->user, $remove->event, $remove->sheet);

        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilityRepository->remove($unavailability);
        }
    }
}
