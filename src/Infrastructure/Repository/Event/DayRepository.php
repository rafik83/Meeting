<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class DayRepository implements DayRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Day $day)
    {
        $this->entityManager->persist($day);
        $this->entityManager->flush($day);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Day $day)
    {
        $this->entityManager->flush($day);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFromEvent(Event $event)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Day::class, 'day')
            ->where('day.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('day')
            ->from(Day::class, 'day')
            ->where('day.event = :event')
            ->orderBy('day.startTime')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findFirstDayByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('day')
            ->from(Day::class, 'day')
            ->where('day.event = :event')
            ->orderBy('day.startTime')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findByEventStartTimeAndEndTime(Event $event, \DateTimeInterface $start, \DateTimeInterface $end): ?Day
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('day')
            ->from(Day::class, 'day')
            ->where('day.startTime = :start')
            ->andWhere('day.endTime = :end')
            ->andWhere('day.event = :event')
            ->setParameters([
                'start' => $start,
                'end' => $end,
                'event' => $event,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
