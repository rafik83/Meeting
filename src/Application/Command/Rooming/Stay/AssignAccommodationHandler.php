<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasNoRemainingOvernightException;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasRemainingOvernight;

class AssignAccommodationHandler
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    /** @var HasRemainingOvernight */
    private $hasRemainingOvernight;

    public function __construct(
        StayRepositoryInterface $stayRepository,
        HasRemainingOvernight $hasRemainingOvernight
    ) {
        $this->stayRepository = $stayRepository;
        $this->hasRemainingOvernight = $hasRemainingOvernight;
    }

    public function handle(AssignAccommodation $assignAccommodation): void
    {
        if (false === $this->hasRemainingOvernight->isSatisfiedBy(
            $assignAccommodation->accommodation,
            $assignAccommodation->arrival,
            $assignAccommodation->departure)
        ) {
            throw new HasNoRemainingOvernightException();
        }

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
