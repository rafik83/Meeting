<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Accommodation;

use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\AccommodationOvernightCapacity;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;

class AddHandler
{
    /** @var AccommodationRepositoryInterface */
    private $accommodationRepository;

    public function __construct(AccommodationRepositoryInterface $accommodationRepository)
    {
        $this->accommodationRepository = $accommodationRepository;
    }

    public function handle(Add $add): void
    {
        $accommodation = new Accommodation($add->event, $add->title);

        foreach ($add->overnightCapacities as $overnightCapacity) {
            $accommodation->addOvernightCapacity(
                new AccommodationOvernightCapacity(
                    $accommodation,
                    $overnightCapacity->date,
                    $overnightCapacity->capacity
                )
            );
        }

        $this->accommodationRepository->add($accommodation);
    }
}
