<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User\Event;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

class ExtraDataRepository implements ExtraDataRepositoryInterface
{
    /** @var EntityManager */
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
    public function add(ExtraData $extraData)
    {
        $this->entityManager->persist($extraData);
        $this->entityManager->flush($extraData);
    }

    /**
     * {@inheritdoc}
     */
    public function set(ExtraData $extraData)
    {
        $this->entityManager->flush($extraData);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ExtraData $extraData): void
    {
        $this->entityManager->remove($extraData);
        $this->entityManager->flush($extraData);
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id): ?ExtraData
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraDataForEventAndName(Event $event, string $name): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.event = :event')
            ->andWhere('extraData.name = :name')
            ->setParameter('event', $event)
            ->setParameter('name', $name)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function getExtraDataForEventNameAndValue(
        Event $event,
        string $name,
        $value
    ): ?ExtraData {
        $queryBuilder = $this->getExtraDataForEventNameQueryBuilder($event, $name);
        $queryBuilder
            ->andWhere('extraData.value = :value')
            ->setParameter('value', $value)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getExtraDataForEventNameAndMD5Value(
        Event $event,
        string $name,
        $value
    ): ?ExtraData {
        $queryBuilder = $this->getExtraDataForEventNameQueryBuilder($event, $name);
        $queryBuilder
            ->andWhere('MD5(extraData.value) = :value')
            ->setParameter('value', $value)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    private function getExtraDataForEventNameQueryBuilder(Event $event, string $name): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.event = :event')
            ->andWhere('extraData.name = :name')
            ->setParameter('event', $event)
            ->setParameter('name', $name)
        ;
    }

    public function getExtraDataForEventIdAndNameIndexedByUserId(int $eventId, string $name): array
    {
        /** @var ExtraData[] $results */
        $results = $this->entityManager->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.event = :eventId')
            ->andWhere('extraData.name = :name')
            ->setParameter('eventId', $eventId)
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult()
        ;

        $extraDataIndexedByUserId = [];

        foreach ($results as $result) {
            $extraDataIndexedByUserId[$result->getUser()->getId()] = $result;
        }

        return $extraDataIndexedByUserId;
    }

    /**
     * {@inheritdoc}
     */
    public function getForEventNameOlderThanDate(
        Event $event,
        string $name,
        \DateTimeInterface $dateTime
    ): array {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('extraData', 'user')
            ->from(ExtraData::class, 'extraData')
            ->join('extraData.user', 'user', 'WITH', 'extraData.event = :event AND extraData.name = :name AND extraData.updatedAt < :date')
            ->setParameter('event', $event)
            ->setParameter('name', $name)
            ->setParameter('date', $dateTime);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getForEventsAndNameWithOlderThanDate(
        array $events,
        string $name,
        \DateTimeInterface $dateTime
    ): array {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('extraData', 'user', 'event')
            ->from(ExtraData::class, 'extraData')
            ->join('extraData.user', 'user', 'WITH', 'extraData.name = :name
                AND extraData.event IN (:events)
                AND extraData.updatedAt < :date
            ')
            ->join('extraData.event', 'event')
            ->setParameter('events', $events)
            ->setParameter('name', $name)
            ->setParameter('date', $dateTime);

        return $queryBuilder->getQuery()->getResult();
    }

    public function removeForUserAndEventAndName(User $user, Event $event, string $name): void
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(ExtraData::class, 'extra_data')
            ->where('extra_data.event = :event')
            ->andWhere('extra_data.user = :user')
            ->andWhere('extra_data.name = :name')
            ->setParameter('event', $event)
            ->setParameter('user', $user)
            ->setParameter('name', $name)
        ;

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraDataForEventNameAndUser(Event $event, string $name, User $user): ?ExtraData
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.event = :event')
            ->andWhere('extraData.name = :name')
            ->andWhere('extraData.user = :user')
            ->setParameter('event', $event)
            ->setParameter('user', $user)
            ->setParameter('name', $name)
            ->orderBy('extraData.updatedAt', 'desc')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getExtraDataForEventIdNameAndUserId(int $eventId, string $name, int $userId): ?ExtraData
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.event = :event')
            ->andWhere('extraData.name = :name')
            ->andWhere('extraData.user = :user')
            ->setParameter('event', $eventId)
            ->setParameter('user', $userId)
            ->setParameter('name', $name)
            ->orderBy('extraData.updatedAt', 'desc')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
