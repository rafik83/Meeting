<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardUserAndTypeScanView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;

class ScanRepository implements ScanRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /** @param EntityManager $entityManager */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Scan $scan): void
    {
        $this->entityManager->persist($scan);
        $this->entityManager->flush($scan);
    }

    public function getUserFirstCheckinTodayByEvent(User $user, Event $event, \DateTimeInterface $dateTime): ?Scan
    {
        $begin = $this->getDayBegin($dateTime);
        $end = $this->getDayEnd($dateTime);

        return
            $this->entityManager->createQueryBuilder()
                ->select('scan')
                ->from(Scan::class, 'scan')
                ->where('scan.event = :event')
                ->andWhere('scan.user = :user')
                ->andWhere('scan.type = :type')
                ->andWhere('scan.scannedAt >= :startAt and scan.scannedAt <= :endAt')
                ->setParameters([
                    'event' => $event,
                    'user' => $user,
                    'startAt' => $begin,
                    'endAt' => $end,
                    'type' => Type::TYPE_EVENT_ENTRANCE,
                ])
                ->addOrderBy('scan.createdAt', 'asc')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;
    }

    public function isUserCheckinTodayByEvent(User $user, Event $event, \DateTimeInterface $dateTime): bool
    {
        $begin = $this->getDayBegin($dateTime);
        $end = $this->getDayEnd($dateTime);

        return
            (int) $this->entityManager->createQueryBuilder()
                ->select('count(scan.id)')
                ->from(Scan::class, 'scan')
                ->where('scan.event = :event')
                ->andWhere('scan.user = :user')
                ->andWhere('scan.type = :type')
                ->andWhere('scan.scannedAt >= :startAt and scan.scannedAt <= :endAt')
                ->setParameters([
                    'event' => $event,
                    'user' => $user,
                    'startAt' => $begin,
                    'endAt' => $end,
                    'type' => Type::TYPE_EVENT_ENTRANCE,
                ])
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    public function getScanDateByUsersAndEvent(array $users, Event $event, \DateTimeInterface $dateTime): array
    {
        $begin = $this->getDayBegin($dateTime);
        $end = $this->getDayEnd($dateTime);

        /** @var Scan[] $scans */
        $scans = $this->entityManager->createQueryBuilder()
                ->select('scan')
                ->from(Scan::class, 'scan')
                ->where('scan.event = :event')
                ->andWhere('scan.user in (:users)')
                ->andWhere('scan.type = :type')
                ->andWhere('scan.scannedAt >= :startAt and scan.scannedAt <= :endAt')
                ->setParameters([
                    'event' => $event,
                    'users' => $users,
                    'startAt' => $begin,
                    'endAt' => $end,
                    'type' => Type::TYPE_EVENT_ENTRANCE,
                ])
                ->getQuery()
                ->getResult();

        $scansIndexedByUserId = [];

        foreach ($scans as $scan) {
            $scansIndexedByUserId[$scan->getUser()->getId()] = $scan;
        }

        return $scansIndexedByUserId;
    }

    public function isUserCheckinByEventAndSlot(User $user, Event $event, MeetingSlot $meetingSlot): bool
    {
        $begin = $this->getDayBegin($meetingSlot->getBegin());
        $end = $this->getDayEnd($meetingSlot->getEnd());

        return
            (int) $this->entityManager->createQueryBuilder()
            ->select('count(scan.id)')
            ->from(Scan::class, 'scan')
            ->where('scan.event = :event')
            ->andWhere('scan.user = :user')
            ->andWhere('scan.type = :type')
            ->andWhere('scan.scannedAt >= :startAt and scan.scannedAt <= :endAt')
            ->setParameters([
                'event' => $event,
                'user' => $user,
                'startAt' => $begin,
                'endAt' => $end,
                'type' => Type::TYPE_EVENT_ENTRANCE,
            ])
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function hasScanForUserEventTypeAndObjectId(
        User $user,
        Event $event,
        string $scanType,
        int $objectId
    ): bool {
        return $this->entityManager->createQueryBuilder()
                ->select('count(scan.id)')
                ->from(Scan::class, 'scan')
                ->where('scan.event = :event')
                ->andWhere('scan.user = :user')
                ->andWhere('scan.type = :type')
                ->andWhere('scan.objectId = :objectId')
                ->setParameters([
                    'event' => $event,
                    'user' => $user,
                    'type' => $scanType,
                    'objectId' => $objectId,
                ])
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult() > 1
        ;
    }

    public function getScanForUserEventTypeAndObjectId(
        User $user,
        Event $event,
        string $scanType,
        int $objectId
    ): ?Scan {
        return $this->entityManager->createQueryBuilder()
            ->select('scan')
            ->from(Scan::class, 'scan')
            ->where('scan.event = :event')
            ->andWhere('scan.user = :user')
            ->andWhere('scan.type = :type')
            ->andWhere('scan.objectId = :objectId')
            ->setParameters([
                'event' => $event,
                'user' => $user,
                'type' => $scanType,
                'objectId' => $objectId,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getUserCheckinByEventAndDay(Event $event, \DateTimeInterface $dateTime): array
    {
        $begin = $this->getDayBegin($dateTime);
        $end = $this->getDayEnd($dateTime);

        return $this->entityManager->createQueryBuilder()
            ->select(
                sprintf(
                    'DISTINCT NEW %s(IDENTITY(scan.user), IDENTITY(sheet.type))',
                    DashboardUserAndTypeScanView::class
                )
            )
            ->from(Scan::class, 'scan')
            ->join(
                'scan.user',
                'user',
                'WITH',
                'scan.event = :event AND scan.type = :type AND scan.scannedAt >= :startAt and scan.scannedAt <= :endAt'
            )
            ->join(Participant::class, 'participant', 'WITH', 'participant.user = user')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->setParameters(
                [
                    'event' => $event,
                    'startAt' => $begin,
                    'endAt' => $end,
                    'type' => Type::TYPE_EVENT_ENTRANCE,
                ]
            )
            ->getQuery()
            ->getResult();
    }

    public function getHappeningParticipantsCount(Event $event): array
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('scan.objectId as happeningId, count(scan.id) as countParticipant')
            ->from(Scan::class, 'scan')
            ->where('scan.event = :event')
            ->setParameter('event', $event)
            ->andWhere('scan.type = :type')
            ->setParameter('type', Type::TYPE_HAPPENING_ENTRANCE)
            ->groupBy('scan.objectId')
            ->getQuery()
            ->getResult();

        $happeningParticipantsCount = [];

        foreach ($result as $count){
            $happeningParticipantsCount[$count ['happeningId']] = (int) $count['countParticipant'];
        }

        return $happeningParticipantsCount;
    }

    private function getDayBegin(\DateTimeInterface $dateTime): \DateTime
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTime(0, 0, 0);
    }

    private function getDayEnd(\DateTimeInterface $dateTime): \DateTime
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTime(23, 59, 59);
    }

}
