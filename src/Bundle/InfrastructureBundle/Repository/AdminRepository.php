<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Admin $admin)
    {
        $this->entityManager->persist($admin);
        $this->entityManager->flush($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function emailExists($email)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin.id')
            ->from('Entity:Admin', 'admin')
            ->where('admin.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult() ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function set(Admin $admin)
    {
        $this->entityManager->flush($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail($email)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from('Entity:Admin', 'admin')
            ->where('admin.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function listPaginated($page, $limit, array $filters)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from(Admin::class, 'admin', 'admin.id')
            ->orderBy('admin.lastname', 'ASC');

        if (isset($filters['role']) && null !== $filters['role'] && in_array($filters['role'], Admin::getAllRoles())) {
            $queryBuilder
                ->where('admin.role = :role')
                ->setParameter('role', $filters['role']);
        }

        if (isset($filters['event']) && null !== $filters['event']) {
            $queryBuilder
                ->leftJoin('admin.events', 'event')
                ->andWhere($queryBuilder->expr()->orX(
                    'event.id = :eventId',
                    'admin.role = :role_event AND admin.events IS EMPTY'
                ))
                ->setParameter('eventId', $filters['event'])
                ->setParameter('role_event', Admin::ROLE_SUPER_ADMIN);
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'admin', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from(Admin::class, 'admin');

        return $queryBuilder->getQuery()->getResult();
    }
}
