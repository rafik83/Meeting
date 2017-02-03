<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;

class MassUnavailabilityViewQueryHandler
{
    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /**
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     */
    public function __construct(MassAssignmentRepositoryInterface $massAssignmentRepository)
    {
        $this->massAssignmentRepository = $massAssignmentRepository;
    }

    /**
     * @param MassUnavailabilityViewQuery $query
     *
     * @return MassUnavailabilityView|null
     */
    public function handle(MassUnavailabilityViewQuery $query)
    {
        $begin = $query->mass->getBegin();
        $end   = $query->mass->getEnd();

        if ($query->mass->isDispatch()) {
            $assignment = $this->massAssignmentRepository->find($query->mass, $query->participant);

            if ($assignment !== null) {
                if (!$assignment->isEnabled()) {
                    return null;
                }

                $begin = $assignment->getBegin();
                $end    = $assignment->getEnd();
            }
        }

        return new MassUnavailabilityView(
            $query->mass->getId(),
            $begin,
            $end,
            $query->mass->getTitle($query->locale),
            $query->mass->getDescription($query->locale),
            $query->mass->getCategory()->getPicto(),
            $query->mass->getCategory()->getLeftColor(),
            $query->mass->getCategory()->getRightColor(),
            $query->event->getTimeZone()
        );
    }
}
