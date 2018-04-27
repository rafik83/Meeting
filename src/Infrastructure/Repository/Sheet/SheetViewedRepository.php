<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;

class SheetViewedRepository implements SheetViewedRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * SheetViewedRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(SheetViewed $sheetViewed)
    {
        $this->entityManager->persist($sheetViewed);
        $this->entityManager->flush($sheetViewed);
    }

    /**
     * {@inheritdoc}
     */
    public function isSheetAlreadySeenByUser(User $user, Sheet $sheet)
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
    public function getSheetsAlreadySeenByUser(User $user, array $sheetIds)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('sheetViewed')
            ->from(SheetViewed::class, 'sheetViewed')
            ->where('sheetViewed.sheet IN (:sheetIds) AND sheetViewed.user = :user')
            ->setParameters([
                'sheetIds' => $sheetIds,
                'user'  => $user,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }
}
