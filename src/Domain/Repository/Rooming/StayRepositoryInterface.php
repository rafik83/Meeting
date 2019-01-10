<?php

namespace Proximum\Vimeet\Domain\Repository\Rooming;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Domain\View\Rooming\AccommodationStayView;
use Proximum\Vimeet\Domain\View\Rooming\StayView;
use Proximum\Vimeet\Domain\View\Rooming\TotalStaysPerPeriod;

interface StayRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return StayView[]
     */
    public function getStayViewsByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return Stay[]
     */
    public function getStaysByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return AccommodationStayView[]
     */
    public function getAccommodationStaysByEvent(Event $event): array;

    public function add(Stay $stay): void;

    public function update(Stay $stay): void;

    public function remove(Stay $stay): void;

    /**
     * @return TotalStaysPerPeriod[]
     */
    public function getTotalStaysByAccommodationPeriod(Accommodation $accommodation): array;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return TimeRangeView[]
     */
    public function getTimeRangeViewsByUserAndEvent(User $user, Event $event): array;
}
