<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Rooming;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\View\Rooming\StayView;

class StayRepository implements StayRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getStaysByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(stay.id, user.id, stay.arrival, stay.departure, accommodation.title, stay.roomType)',
                    StayView::class
                )
            )
            ->from(Stay::class, 'stay')
            ->join('stay.accommodation', 'accommodation', 'WITH', 'stay.event = :event')
            ->join('stay.users', 'user')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();
    }

    public function add(Stay $stay): void
    {
        $this->entityManager->persist($stay);
        $this->entityManager->flush($stay);
    }
}
