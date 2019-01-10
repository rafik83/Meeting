<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Rooming;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Domain\View\Rooming\AccommodationStayView;
use Proximum\Vimeet\Domain\View\Rooming\StayView;
use Proximum\Vimeet\Domain\View\Rooming\TotalStaysPerPeriod;

class StayRepository implements StayRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getStayViewsByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(stay.id, user.id, stay.arrival, stay.departure, accommodation.title, stay.roomType, stay.roomNumber)',
                    StayView::class
                )
            )
            ->from(Stay::class, 'stay')
            ->join('stay.accommodation', 'accommodation', 'WITH', 'stay.event = :event')
            ->join('stay.users', 'user')
            ->orderBy('stay.arrival')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Event $event
     *
     * @return Stay[]
     */
    public function getStaysByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('stay, user, accommodation')
            ->from(Stay::class, 'stay')
            ->join('stay.accommodation', 'accommodation', 'WITH', 'stay.event = :event')
            ->join('stay.users', 'user')
            ->orderBy('stay.arrival')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }


    public function getAccommodationStaysByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(stay.id, stay.arrival, stay.departure, accommodation.id)',
                    AccommodationStayView::class
                )
            )
            ->from(Stay::class, 'stay')
            ->join('stay.accommodation', 'accommodation', 'WITH', 'stay.event = :event')
            ->orderBy('stay.arrival')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    public function add(Stay $stay): void
    {
        $this->entityManager->persist($stay);
        $this->entityManager->flush($stay);
    }

    public function update(Stay $stay): void
    {
        $this->entityManager->flush($stay);
    }

    public function remove(Stay $stay): void
    {
        $this->entityManager->remove($stay);
        $this->entityManager->flush($stay);
    }

    public function getTotalStaysByAccommodationPeriod(Accommodation $accommodation): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select(sprintf('new %s(stay.arrival, stay.departure, count(stay.id))', TotalStaysPerPeriod::class))
            ->from(Stay::class, 'stay')
            ->where('stay.accommodation = :accommodation')
            ->setParameter('accommodation', $accommodation)
            ->groupBy('stay.arrival, stay.departure')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return TimeRangeView[]
     */
    public function getTimeRangeViewsByUserAndEvent(User $user, Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(stay.arrival, stay.departure)',
                    TimeRangeView::class
                )
            )
            ->from(Stay::class, 'stay')
            ->join('stay.users', 'user', 'WITH', 'stay.event = :event AND user = :user')
            ->setParameters(
                [
                    'event' => $event,
                    'user' => $user,
                ]
            )
            ->getQuery()
            ->getResult();
    }
}
