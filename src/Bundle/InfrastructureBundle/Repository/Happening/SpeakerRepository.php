<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository\Happening;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;

class SpeakerRepository implements SpeakerRepositoryInterface
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
    public function add(Speaker $speaker)
    {
        $this->entityManager->persist($speaker);
        $this->entityManager->flush($speaker);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Speaker $speaker)
    {
        $this->entityManager->flush($speaker);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('speaker')
            ->from(Speaker::class, 'speaker');

        return $queryBuilder->getQuery()->getResult();
    }
}
