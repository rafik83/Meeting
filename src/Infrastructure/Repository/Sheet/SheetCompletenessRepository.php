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
use Proximum\Vimeet\Domain\Model\SheetCompleteness;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;

class SheetCompletenessRepository implements SheetCompletenessRepositoryInterface
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
    public function add(SheetCompleteness $sheetCompleteness)
    {
        $this->entityManager->persist($sheetCompleteness);
        $this->entityManager->flush($sheetCompleteness);
    }

    /**
     * {@inheritdoc}
     */
    public function findCompleteness(Sheet $sheet, $locale)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('completeness')
            ->from(SheetCompleteness::class, 'completeness')
            ->where('completeness.sheet = :sheet')
            ->andWhere('completeness.locale = :locale')
            ->setParameter('sheet', $sheet)
            ->setParameter('locale', $locale)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeForSheet(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(SheetCompleteness::class, 'completeness')
            ->where('completeness.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        $queryBuilder->getQuery()->execute();
    }
}
