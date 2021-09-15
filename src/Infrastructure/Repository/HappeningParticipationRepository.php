<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;

class HappeningParticipationRepository implements HappeningParticipationRepositoryInterface
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
    public function add(HappeningParticipation $happeningParticipation)
    {
        $this->entityManager->persist($happeningParticipation);
        $this->entityManager->flush($happeningParticipation);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(HappeningParticipation $happeningParticipation)
    {
        $this->entityManager->remove($happeningParticipation);
        $this->entityManager->flush($happeningParticipation);
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user, Event $event, bool $excludeDisabled, bool $onlyVisible = false): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user = :user'
            )
            ->setParameter('user', $user)
            ->setParameter('event', $event)
        ;

        if ($excludeDisabled) {
            $queryBuilder->andWhere('participation.disabled = false');
        }

        if ($onlyVisible) {
            $queryBuilder->andWhere('participation.visible = true');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasParticipationForUserAndEvent(User $user, Event $event): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation.id')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user = :user AND participation.disabled = false'
            )
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndUsers(Event $event, array $users)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation, happening')
            ->from(HappeningParticipation::class, 'participation')
            ->join(
                'participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user IN (:users) AND participation.disabled = false'
            )
            ->setParameter('users', $users)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipants(array $participants, Event $event)
    {
        $users = array_map(function (Participant $participant) {
            return $participant->getUser();
        }, $participants);
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation, happening')
            ->from(HappeningParticipation::class, 'participation')
            ->join(
                'participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user IN (:users)  AND participation.disabled = false'
            )
            ->setParameter('users', $users)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $sheetUsers = $this->getUsersOnSheet($sheet);

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation, happening')
            ->from(HappeningParticipation::class, 'participation')
            ->join(
                'participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user IN (:users) AND participation.disabled = false'
            )
            ->setParameter('event', $sheet->getEvent())
            ->setParameter('users', $sheetUsers);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function checkAnyParticipation(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation.id')
            ->from(HappeningParticipation::class, 'participation')
            ->join(
                'participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user = :user AND participation.disabled = false')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByUserAndEvent(User $user, Event $event): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participation.id)')
            ->from(HappeningParticipation::class, 'participation')
            ->join(
                'participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user = :user AND participation.disabled = false')
            ->setParameter('user', $user)
            ->setParameter('event', $event);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countParticipationByHappening(Happening $happening)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participation)')
            ->from(HappeningParticipation::class, 'participation')
            ->where('participation.happening  = :happening')
            ->andWhere('participation.disabled = false')
            ->setParameter('happening', $happening);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation, happening')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening', 'happening')
            ->where('happening.event = :event')
            ->andWhere('participation.disabled = false')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countParticipationByEvent(Event $event)
    {
        $participationCounts = [];

        $results = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening.id, COUNT(participation)')
            ->from(HappeningParticipation::class, 'participation')
            ->where('participation.disabled = false')
            ->join('participation.happening', 'happening', 'WITH', 'happening = participation.happening')
            ->join('happening.event', 'event', 'WITH', 'event = :event')
            ->setParameter('event', $event)
            ->groupBy('happening.id')
            ->getQuery()
            ->getResult();

        //  Reformat the array to key (happening id) => value (count participation)
        foreach ($results as $result) {
            $participationCounts[$result['id']] = $result[1];
        }

        return $participationCounts;
    }

    /**
     * {@inheritdoc}
     */
    public function countParticipationsBySheet(Sheet $sheet)
    {
        $sheetUsers = $this->getUsersOnSheet($sheet);

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('COUNT(participation.id)')
            ->from(HappeningParticipation::class, 'participation')
            ->join(
                'participation.happening',
                'happening',
                'WITH',
                'happening.event = :event AND participation.user IN (:users) AND participation.disabled = false'
            )
            ->setParameter('users', $sheetUsers)
            ->setParameter('event', $sheet->getEvent());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasParticipationsBySheet(Sheet $sheet)
    {
        return $this->countParticipationsBySheet($sheet) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipationsForSheet(Sheet $sheet, array $happenings)
    {
        $sheetUsers = $this->getUsersOnSheet($sheet);

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->join(
                'participation.happening',
                'happening',
                'WITH',
                'happening IN (:happenings) AND participation.user IN (:users) AND participation.disabled = false'
            )
            ->setParameter('happenings', $happenings)
            ->setParameter('users', $sheetUsers);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeUserForHappening(User $user, Happening $happening)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(HappeningParticipation::class, 'participation')
            ->where('participation.user = :user')
            ->andWhere('participation.disabled = false')
            ->andWhere('participation.happening = :happening')
            ->setParameter('user', $user)
            ->setParameter('happening', $happening)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(HappeningParticipation $happeningParticipation)
    {
        $this->entityManager->flush($happeningParticipation);
    }

    /**
     * @param Sheet $sheet
     *
     * @return User[]
     */
    private function getUsersOnSheet(Sheet $sheet)
    {
        $sheetUsers = array_map(function (Participant $participant) {
            return $participant->getUser();
        }, $sheet->getParticipants()->toArray());

        return $sheetUsers;
    }

    /**
     * {@inheritdoc}
     */
    public function findByHappeningAndUser(Happening $happening, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->where('participation.happening = :happening AND participation.user = :user')
            ->setMaxResults(1)
            ->setParameters([
                'happening' => $happening,
                'user'      => $user,
            ]);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByHappening(Happening $happening): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->where('participation.happening = :happening')
            ->setParameter('happening', $happening)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySpeaker(User $user, Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening')
            ->from(Happening::class, 'happening')
            ->join('happening.talkings', 'talking', 'WITH', 'happening.event = :event')
            ->join('talking.speaker', 'speaker', 'WITH', 'speaker.user = :user')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
        ;

        $happeningParticipations = [];

        foreach ($queryBuilder->getQuery()->getResult() as $happening) {
            $happeningParticipations[] = new HappeningParticipation($happening, $user);
        }

        return $happeningParticipations;
    }

    public function hasHappeningParticipant(Event $event): bool
    {
        return null !== $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation.id')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening', 'happening', 'WITH', 'happening.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPreviousMandatoryEvaluation(Event $event, User $user, \DateTimeInterface $begin): ?HappeningParticipation {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participation')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening', 'happening', 'WITH', 'happening.event = :event')
            ->join(Scan::class, 'scan', 'WITH', 'scan.type = :scan_type AND happening = scan.objectId AND scan.user = :user')
            ->setParameter('event', $event)
            ->andWhere('participation.user = :user')
            ->setParameter('user', $user)
            ->andWhere('happening.begin < :begin')
            ->setParameter('begin', $begin)
            ->setParameter('scan_type', Type::TYPE_HAPPENING_ENTRANCE)
            ->andWhere('happening.mustEvaluateHappening = true')
            ->andWhere('participation.evaluation is null')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function set(HappeningParticipation $happeningParticipation): void
    {
        $this->entityManager->flush($happeningParticipation);
    }

    public function getEvaluationsCount(Event $event): array
    {
         $result = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening.id as happeningId, COUNT(participation.evaluation) as countEvaluation')
            ->from(HappeningParticipation::class, 'participation', 'participation.id')
            ->join('participation.happening', 'happening', 'WITH', 'happening.event = :event')
            ->setParameter('event', $event)
            ->groupBy('happening.id')
            ->getQuery()
            ->getResult();

         $happeningEvaluationsCounts = [];

         foreach ($result as $evaluation){
             $happeningEvaluationsCounts[$evaluation['happeningId']] = (int) $evaluation['countEvaluation'];
         }

        return $happeningEvaluationsCounts;
    }

    public function getEvaluationsAverage(Event $event): array
    {
        $result = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening.id as happeningId, AVG(participation.evaluation) as averageEvaluation')
            ->from(HappeningParticipation::class, 'participation', 'participation.id')
            ->join('participation.happening', 'happening', 'WITH', 'happening.event = :event')
            ->setParameter('event', $event)
            ->groupBy('happening.id')
            ->getQuery()
            ->getResult();

        $happeningEvaluationsAverage = [];

        foreach ($result as $evaluation){
            $happeningEvaluationsAverage[$evaluation['happeningId']] = (float) $evaluation['averageEvaluation'];
        }

        return $happeningEvaluationsAverage;
    }

    public function getScannedAt(HappeningParticipation $participation): ?\DateTimeInterface
    {
        $result = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('scan.scannedAt')
            ->from(HappeningParticipation::class, 'participation')
            ->join('participation.happening' , 'happening')
            ->join(Scan::class, 'scan', 'WITH', 'scan.type = :scan_type AND happening.id = scan.objectId AND scan.user = participation.user')
            ->andWhere('participation = :participation')
            ->setParameter('participation', $participation)
            ->setParameter('scan_type', Type::TYPE_HAPPENING_ENTRANCE)
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return $result[0]['scannedAt'] ?? null;
    }
}
