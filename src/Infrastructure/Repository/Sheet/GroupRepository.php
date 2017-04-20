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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;

class GroupRepository implements GroupRepositoryInterface
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
     * @param int $id
     *
     * @return null|Group
     */
    public function getById($id)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndManager(Event $event, User $manager)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.manager = :manager AND sheetsGroup.event = :event')
            ->setParameter('manager', $manager)
            ->setParameter('event', $event)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByEventOrderedByTitle(Event $event)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.event = :event')
            ->setParameter('event', $event)
            ->orderBy('sheetsGroup.title', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
