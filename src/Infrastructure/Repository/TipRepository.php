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
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipRepository implements TipRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;
    
    /** @var Paginator */
    private $paginator;
    
    /**
     * SpotUnavailabilityRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
    }
    
    /** {@inheritdoc} */
    public function paginate($page, $limit = 20)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip')
            ->orderBy('tip.id');
    
        return $this->paginator->paginate($queryBuilder, $page, $limit, 'tip', 'id');
    }
    
    /** {@inheritdoc} */
    public function add(Tip $tip)
    {
        // TODO: Implement add() method.
    }
    
    /** {@inheritdoc} */
    public function getById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip')
            ->where('tip.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
