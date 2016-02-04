<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SpotRepository implements SpotRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * SpotRepository constructor.
     *
     * @param EntityManager           $entityManager
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(
        EntityManager $entityManager,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->entityManager  = $entityManager;
        $this->typeRepository = $typeRepository;
    }


    /**
     * @param Spot $spot
     */
    public function add(Spot $spot)
    {
        $this->entityManager->persist($spot);
        $this->entityManager->flush($spot);
    }

    /**
     * @param Spot $spot
     */
    public function set(Spot $spot)
    {
        $this->entityManager->flush($spot);
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('spot')
            ->from(Spot::class, 'spot')
            ->where('spot.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}