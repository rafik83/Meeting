<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class AssignAccommodationHandler
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(StayRepositoryInterface $stayRepository)
    {
        $this->stayRepository = $stayRepository;
    }

    public function handle(AssignAccommodation $assignAccommodation): void
    {
        $stay = new Stay(
            $assignAccommodation->event,
            $assignAccommodation->user,
            $assignAccommodation->arrival,
            $assignAccommodation->departure,
            $assignAccommodation->accommodation,
            $assignAccommodation->roomType
        );

        if (Stay::ROOM_TYPE_DOUBLE === $assignAccommodation->roomType
            && $assignAccommodation->roommate instanceof User
        ) {
            $stay->addUser($assignAccommodation->roommate);
        }

        $this->stayRepository->add($stay);
    }
}
