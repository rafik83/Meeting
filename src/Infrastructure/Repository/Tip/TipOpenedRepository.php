<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Tip;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipOpened;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Tip\TipOpenedRepositoryInterface;

class TipOpenedRepository implements TipOpenedRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(TipOpened $tipOpened): void
    {
        $this->entityManager->persist($tipOpened);
        $this->entityManager->flush($tipOpened);
    }

    public function isOpened(Tip $tip, User $user): bool
    {
        return null !== $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tipOpened.openedAt')
            ->from(TipOpened::class, 'tipOpened')
            ->where('tipOpened.user = :user AND tipOpened.tip = :tip')
            ->setParameter('user', $user)
            ->setParameter('tip', $tip)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
