<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;

class SheetViewedRepository implements SheetViewedRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(SheetViewed $sheetViewed): void
    {
        $this->entityManager->persist($sheetViewed);
        $this->entityManager->flush($sheetViewed);
    }

    /**
     * {@inheritdoc}
     */
    public function isSheetAlreadySeenByUser(User $user, Sheet $sheet): bool
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('sheetViewed.id')
            ->from(SheetViewed::class, 'sheetViewed')
            ->where('sheetViewed.sheet = :sheet AND sheetViewed.user = :user')
            ->setParameters([
                'sheet' => $sheet,
                'user'  => $user,
            ])
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsAlreadySeenByUser(User $user, array $sheetIds): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('sheetViewed')
            ->from(SheetViewed::class, 'sheetViewed')
            ->where('sheetViewed.sheet IN (:sheetIds) AND sheetViewed.user = :user')
            ->setParameters([
                'sheetIds' => $sheetIds,
                'user' => $user,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getSheetsSeenByUserAndEvent(User $user, Event $event): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from(SheetViewed::class, 'sheetViewed')
            ->leftJoin('sheetViewed.sheet', 'sheet')
            ->where('sheetViewed.user = :user')
            ->andWhere('sheet.event = :event')
            ->setParameters([
                'user' => $user,
                'event' => $event,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getUsersWhoViewedSheet(Sheet $sheet): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('user.id')
            ->from(SheetViewed::class, 'sheetViewed')
            ->leftJoin('sheetViewed.user', 'user')
            ->where('sheetViewed.sheet = :sheet')
            ->setParameter('sheet', $sheet)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return int[]
     */
    public function getSheetIdsWhoViewedSheet(Sheet $sheet): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->distinct()
            ->from(Sheet::class, 'sheet')
            ->join('sheet.participants', 'participant')
            ->join(SheetViewed::class, 'sheetView', 'WITH', 'participant.user = sheetView.user')
            ->where('sheetView.sheet = :sheet')
            ->andWhere('sheet.event = :event')
            ->setParameter('sheet', $sheet)
            ->setParameter('event', $sheet->getEvent())
        ;

        $participantUserIds = array_column($queryBuilder->getQuery()->getArrayResult(), 'id');

        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->distinct()
            ->from(Sheet::class, 'sheet')
            ->join(SheetViewed::class, 'sheetView', 'WITH', 'sheet.owner = sheetView.user')
            ->where('sheetView.sheet = :sheet')
            ->andWhere('sheet.event = :event')
            ->setParameter('sheet', $sheet)
            ->setParameter('event', $sheet->getEvent())
        ;

        $ownerUserIds = array_column($queryBuilder->getQuery()->getArrayResult(), 'id');

        return array_unique(array_merge($participantUserIds, $ownerUserIds));
    }
}
