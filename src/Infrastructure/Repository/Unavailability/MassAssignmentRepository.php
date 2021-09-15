<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Unavailability;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;

class MassAssignmentRepository implements MassAssignmentRepositoryInterface
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
    public function add(MassAssignment $massAssignment)
    {
        $this->entityManager->persist($massAssignment);
        $this->entityManager->flush($massAssignment);
        $this->entityManager->detach($massAssignment);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Mass $mass, Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment')
            ->from(MassAssignment::class, 'assignment')
            ->where('assignment.mass = :mass')
            ->andWhere('assignment.user = :user')
            ->setParameter('mass', $mass)
            ->setParameter('user', $participant->getUser())
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, user')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.mass', 'mass', 'WITH', 'mass.event = :event')
            ->join('assignment.user', 'user')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, user')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.mass', 'mass', 'WITH', 'mass.event = :event')
            ->join('assignment.user', 'user')
            ->where('assignment.enabled = true')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function set(MassAssignment $massAssignment)
    {
        $this->entityManager->flush($massAssignment);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $sheetUsers = array_map(function (Participant $participant) {
            return $participant->getUser();
        }, $sheet->getParticipants()->toArray());

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, user')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.user', 'user', 'WITH', 'user IN (:sheetUsers)')
            ->join('assignment.mass', 'mass', 'WITH', 'mass.event = :event')
            ->setParameter('event', $sheet->getEvent())
            ->setParameter('sheetUsers', $sheetUsers)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipant(Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, user')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.user', 'user', 'WITH', 'user = :user')
            ->join('assignment.mass', 'mass')
            ->join('assignment.mass', 'mass', 'WITH', 'mass.event = :event')
            ->setParameter('event', $participant->getSheet()->getEvent())
            ->setParameter('user', $participant->getUser());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByParticipant(Participant $participant)
    {
        return $this->findEnabledByUserAndEvent($participant->getUser(), $participant->getSheet()->getEvent());
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByUserAndEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.mass', 'mass', 'WITH', 'assignment.user = :user AND mass.event = :event AND assignment.enabled = true')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByParticipants(array $participants)
    {
        $event = null;

        $users = array_map(function (Participant $participant) {
            return $participant->getUser();
        }, $participants);

        $firstParticipant = reset($participants);

        if ($firstParticipant instanceof Participant) {
            $event = $firstParticipant->getSheet()->getEvent();
        }

        return $this->findEnabledByEventAndUsers($event, $users);
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByEventAndUsers(Event $event, array $users)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('assignment, mass, user')
            ->from(MassAssignment::class, 'assignment')
            ->join('assignment.user', 'user', 'WITH', 'user IN (:users) AND assignment.enabled = true')
            ->join('assignment.mass', 'mass', 'WITH', 'mass.event = :event')
            ->setParameter('event', $event)
            ->setParameter('users', $users);

        return $queryBuilder->getQuery()->getResult();
    }

    public function removeByUserAndMass($user, Mass $mass): void
    {
         $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(MassAssignment::class, 'massAssignment')
            ->where('massAssignment.user = :user')
            ->andWhere('massAssignment.mass = :mass')
            ->setParameter('user', $user)
            ->setParameter('mass', $mass)
            ->getQuery()
            ->execute();
    }
}
