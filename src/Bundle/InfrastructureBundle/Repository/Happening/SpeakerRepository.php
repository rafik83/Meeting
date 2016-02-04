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
     * @param Speaker $speaker
     */
    public function add(Speaker $speaker)
    {
        $this->entityManager->persist($speaker);
        $this->entityManager->flush($speaker);
    }

    /**
     * @param Speaker $speaker
     */
    public function set(Speaker $speaker)
    {
        $this->entityManager->flush($speaker);
    }
}
