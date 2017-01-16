<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class BillingInfoRepository implements BillingInfoRepositoryInterface
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
    public function getBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('billing_info')
            ->from(BillingInfo::class, 'billing_info')
            ->where('billing_info.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getBySheets($sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('billing_info')
            ->from(BillingInfo::class, 'billing_info')
            ->where('billing_info.sheet IN (:sheets)')
            ->setParameter('sheets', $sheets);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(BillingInfo $billingInfo)
    {
        $this->entityManager->persist($billingInfo);
        $this->entityManager->flush($billingInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function set(BillingInfo $billingInfo)
    {
        $this->entityManager->flush($billingInfo);
    }
}
