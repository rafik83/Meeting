<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\HappeningParticipation;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class EnableDisableManager
{
    const DISABLE_HAPPENING_PARTICIPATION = false;
    const ENABLE_HAPPENING_PARTICIPATION  = true;

    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * Disable constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param SheetRepositoryInterface                  $sheetRepository
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->sheetRepository                  = $sheetRepository;
    }

    /**
     * @param Sheet $sheet
     * @param bool  $state
     */
    public function update(Sheet $sheet, $state)
    {
        foreach ($sheet->getParticipants() as $participant) {
            $user  = $participant->getUser();
            $event = $sheet->getEvent();

            $happeningParticipations = $this
                ->happeningParticipationRepository
                ->findByUser($user, $event);

            $hasParticipationToActiveSheet = $this->sheetRepository->hasParticipationToActiveSheet($user, $event);

            if (!$hasParticipationToActiveSheet) {
                foreach ($happeningParticipations as $participation) {

                    /*
                     * State depends of the enable/disable batch command :
                     *
                     * - a TRUE state is in case of enable
                     * so we need to enable participations (!true === false)
                     *
                     * - a FALSE state is in case of disable
                     * so we need to disable participations (!false === true)
                     */
                    $this->happeningParticipationRepository->update($participation->setDisabled(!$state));
                }
            }
        }
    }
}
