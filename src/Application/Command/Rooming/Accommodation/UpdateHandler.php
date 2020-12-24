<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Accommodation;

use Proximum\Vimeet\Domain\Model\Rooming\AccommodationOvernightCapacity;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;

class UpdateHandler
{
    /** @var AccommodationRepositoryInterface */
    private $accommodationRepository;

    public function __construct(AccommodationRepositoryInterface $accommodationRepository)
    {
        $this->accommodationRepository = $accommodationRepository;
    }

    public function handle(Update $update): void
    {
        $accommodation = $update->accommodation;
        $overnightCapacities = [];

        foreach ($update->overnightCapacities as $overnightCapacity) {
            $overnightCapacities[] = new AccommodationOvernightCapacity(
                $accommodation,
                $overnightCapacity->date,
                $overnightCapacity->capacity
            );
        }

        $accommodation->update($update->title, $overnightCapacities);

        $this->accommodationRepository->update($accommodation);
    }
}
