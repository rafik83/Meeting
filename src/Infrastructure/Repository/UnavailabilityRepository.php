<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class UnavailabilityRepository implements UnavailabilityRepositoryInterface
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
    public function add(Unavailability $unavailability)
    {
        $this->entityManager->persist($unavailability);
        $this->entityManager->flush($unavailability);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Unavailability $unavailability)
    {
        $this->entityManager->flush($unavailability);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Unavailability $unavailability)
    {
        $this->entityManager->remove($unavailability);
        $this->entityManager->flush($unavailability);
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipant(Participant $participant)
    {
        return $this->findByUserAndEvent($participant->getUser(), $participant->getSheet()->getEvent());
    }

    /**
     * {@inheritdoc}
     */
    public function findByUserAndEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->where('unavailability.user = :user AND unavailability.event = :event')
            ->setParameters(['user' => $user, 'event' => $event])
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByUserAndEventCreatedByUser(User $user, Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->where('unavailability.user = :user AND unavailability.event = :event AND unavailability.createdBy = :createdBy')
            ->setParameters([
                'user' => $user,
                'event' => $event,
                'createdBy' => Unavailability::CREATED_BY_USER,
            ])
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeSystemUnavailabilityForUserAndEvent(User $user, Event $event): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(Unavailability::class, 'unavailability')
            ->where('unavailability.user = :user AND unavailability.event = :event AND unavailability.createdBy = :createdBy')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setParameter('createdBy', Unavailability::CREATED_BY_SYSTEM)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipants(array $participants)
    {
        /** @var Participant|false $firstParticipant */
        $firstParticipant = reset($participants);

        if (false === $firstParticipant) {
            return [];
        }

        $event = $firstParticipant->getSheet()->getEvent();

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->join(
                Participant::class,
                'participant',
                'WITH',
                'participant IN (:participants) AND participant.user = unavailability.user AND unavailability.event = :event'
            )
            ->setParameters(['participants' => $participants, 'event' => $event])
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndUsers(Event $event, array $users)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->where('unavailability.event = :event AND unavailability.user IN (:users)')
            ->setParameters(['users' => $users, 'event' => $event])
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->join(
                Participant::class,
                'participant',
                'WITH',
                'participant.sheet = :sheet AND participant.user = unavailability.user AND unavailability.event = :event'
            )
            ->setParameters(['sheet' => $sheet, 'event' => $sheet->getEvent()])
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->join(
                Participant::class,
                'participant',
                'WITH',
                'participant.user = unavailability.user AND unavailability.event = :event'
            )
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(unavailability)')
            ->from(Unavailability::class, 'unavailability')
            ->where('unavailability.user = :user AND unavailability.event = :event')
            ->setParameters(['user' => $participant->getUser(), 'event' => $participant->getSheet()->getEvent()])
        ;

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getOverlapUnavailabilities(Unavailability $unavailability)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder();

        $queryBuilder
            ->select('unavailability')
            ->from(Unavailability::class, 'unavailability')
            ->where('unavailability.user = :user AND unavailability.event = :event')
            ->setParameters(['user' => $unavailability->getUser(), 'event' => $unavailability->getEvent()])
            ->andWhere($queryBuilder->expr()->orX(
                'unavailability.begin BETWEEN :begin AND :end',
                'unavailability.end BETWEEN :begin AND :end',
                ':begin BETWEEN unavailability.begin AND unavailability.end',
                ':end BETWEEN unavailability.begin AND unavailability.end'
            ))
            ->setParameter('begin', $unavailability->getBegin())
            ->setParameter('end', $unavailability->getEnd());

        if (null !== $unavailability->getId()) {
            $queryBuilder
                ->andWhere('unavailability.id != :id')
                ->setParameter('id', $unavailability->getId());
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
