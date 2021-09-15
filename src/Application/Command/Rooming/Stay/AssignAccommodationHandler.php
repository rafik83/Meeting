<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasNoRemainingOvernightException;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasRemainingOvernight;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriod;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriodException;
use Proximum\Vimeet\Domain\Rooming\Stay\RoommateHasStayForPeriodException;
use Proximum\Vimeet\Domain\Time\MidnightTransformer;

class AssignAccommodationHandler
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    /** @var HasRemainingOvernight */
    private $hasRemainingOvernight;

    /** @var HasStayForPeriod */
    private $hasStayForPeriod;

    public function __construct(
        StayRepositoryInterface $stayRepository,
        HasRemainingOvernight $hasRemainingOvernight,
        HasStayForPeriod $hasStayForPeriod
    ) {
        $this->stayRepository = $stayRepository;
        $this->hasRemainingOvernight = $hasRemainingOvernight;
        $this->hasStayForPeriod = $hasStayForPeriod;
    }

    public function handle(AssignAccommodation $assignAccommodation): void
    {
        if (false === $this->hasRemainingOvernight->isSatisfiedBy(
                $assignAccommodation->accommodation,
                $assignAccommodation->arrival,
                $assignAccommodation->departure
            )
        ) {
            throw new HasNoRemainingOvernightException();
        }

        if (true === $this->hasStayForPeriod->isSatisfiedBy(
                $assignAccommodation->event,
                $assignAccommodation->user,
                $assignAccommodation->arrival,
                $assignAccommodation->departure
            )
        ) {
            throw new HasStayForPeriodException(
                'The user to assign has a stay on this period'
            );
        }

        $stay = new Stay(
            $assignAccommodation->event,
            $assignAccommodation->user,
            MidnightTransformer::getDateAtMidnight($assignAccommodation->arrival),
            MidnightTransformer::getDateAtMidnight($assignAccommodation->departure),
            $assignAccommodation->accommodation,
            $assignAccommodation->roomType,
            $assignAccommodation->roomNumber
        );

        if ($this->isRoomTypeDoubleOrTwin($assignAccommodation)
            && $assignAccommodation->roommate instanceof User
        ) {
            if (true === $this->hasStayForPeriod->isSatisfiedBy(
                    $assignAccommodation->event,
                    $assignAccommodation->roommate,
                    $assignAccommodation->arrival,
                    $assignAccommodation->departure
                )
            ) {
                throw new RoommateHasStayForPeriodException(
                    'The roommate to assign has a stay on this period'
                );
            }

            $stay->addUser($assignAccommodation->roommate);
        }

        $this->stayRepository->add($stay);
    }

    private function isRoomTypeDoubleOrTwin(AssignAccommodation $assignAccommodation): bool
    {
        return $assignAccommodation->roomType === Stay::ROOM_TYPE_DOUBLE
            || $assignAccommodation->roomType === Stay::ROOM_TYPE_TWIN;
    }
}
