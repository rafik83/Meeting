<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Happening\Participation;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class DisableEnableParticipation
{
    /** @var HappeningParticipationRepositoryInterface */
    private $participationRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param HappeningParticipationRepositoryInterface $participationRepository
     * @param SheetRepositoryInterface                  $sheetRepository
     * @param JobQueueInterface                         $jobQueue
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $participationRepository,
        SheetRepositoryInterface $sheetRepository,
        JobQueueInterface $jobQueue
    ) {
        $this->participationRepository = $participationRepository;
        $this->sheetRepository = $sheetRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * This methods disable / enable participation of happening for Type that have access
     *
     * @param Happening $happening
     */
    public function resolveParticipations(Happening $happening)
    {
        $types = $happening->getTypes();
        $participations = $this->participationRepository->findByHappening($happening);

        foreach ($participations as $participation) {
            $participationPreviousState = $participation->isDisabled();
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent(
                $participation->getUser(),
                $happening->getEvent()
            );

            $typeFound = false;

            foreach ($sheets as $sheet) {
                if ($sheet->attend() && in_array($sheet->getType(), $types, true)) {
                    $participation->setDisabled(false);
                    $typeFound = true;

                    break;
                }
            }

            if ($typeFound === false) {
                $participation->setDisabled(true);
            }


            if ($participationPreviousState !== $participation->isDisabled()) {
                $this->participationRepository->update($participation);

                foreach ($sheets as $sheet) {
                    $this->jobQueue->aggregateSheetAvailableSlot($sheet);
                }
            }
        }
    }
}
