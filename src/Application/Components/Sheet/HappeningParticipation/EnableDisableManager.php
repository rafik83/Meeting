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

class EnableDisableManager
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * Disable constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     */
    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    /**
     * @param Sheet $sheet
     * @param bool  $state
     */
    public function update(Sheet $sheet, $state)
    {
        foreach ($sheet->getParticipants() as $participant) {
            $happeningParticipations = $this
                ->happeningParticipationRepository
                ->findByParticipant($participant);

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
