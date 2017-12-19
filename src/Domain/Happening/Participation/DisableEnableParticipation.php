<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Happening\Participation;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class DisableEnableParticipation
{
    /** @var HappeningParticipationRepositoryInterface */
    private $participationRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param HappeningParticipationRepositoryInterface $participationRepository
     * @param SheetRepositoryInterface                  $sheetRepository
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $participationRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->participationRepository = $participationRepository;
        $this->sheetRepository = $sheetRepository;
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

            $this->participationRepository->update($participation);
        }
    }
}
