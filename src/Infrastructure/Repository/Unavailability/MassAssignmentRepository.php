<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Unavailability;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;

class MassAssignmentRepository implements MassAssignmentRepositoryInterface
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
    public function add(MassAssignment $massAssignment)
    {
        $this->entityManager->persist($massAssignment);
        $this->entityManager->flush($massAssignment);
        $this->entityManager->detach($massAssignment);
    }
}
