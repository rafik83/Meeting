<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Service\MarkdownFormatter;

class SheetPlanningFormatter
{
    /**
     * @var ParticipantPlanningFormatter
     */
    private $participantPlanningFormatter;

    /**
     * @var UnallocatedFormatter
     */
    private $unallocatedFormatter;

    /**
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param UnallocatedFormatter         $unallocatedFormatter
     */
    public function __construct(
        ParticipantPlanningFormatter $participantPlanningFormatter,
        UnallocatedFormatter $unallocatedFormatter
    ) {
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->unallocatedFormatter         = $unallocatedFormatter;
    }

    /**
     * Format the planning of a sheet with the ability to force the first planning to be display to be given as a parameter
     *
     * @param Sheet            $sheet
     * @param string           $locale
     * @param Participant|null $firstParticipantToDisplay
     *
     * @return string
     */
    public function format(Sheet $sheet, $locale, Participant $firstParticipantToDisplay = null)
    {
        $planning = '';

        if ($firstParticipantToDisplay !== null) {
            $planning .= MarkdownFormatter::newLine(
                $this->participantPlanningFormatter->formatPlanningFromParticipant(
                    $firstParticipantToDisplay,
                    $locale
                )
            );
        }

        foreach ($sheet->getParticipants()->toArray() as $participant) {
            if ($participant === $firstParticipantToDisplay) {
                continue;
            }

            $planning .= MarkdownFormatter::newLine(
                $this->participantPlanningFormatter->formatPlanningFromParticipant($participant, $locale)
            );
        }

        return $planning;
    }

    /**
     * Format the planning of a sheet with the ability to force the first planning to be display to be
     * given as a parameter with the unallocated meetings
     *
     * @param Sheet            $sheet
     * @param string           $locale
     * @param Participant|null $firstParticipantToDisplay
     *
     * @return string
     */
    public function formatWithUnallocated(Sheet $sheet, $locale, Participant $firstParticipantToDisplay = null)
    {
        $planning    = $this->format($sheet, $locale, $firstParticipantToDisplay);
        $unallocated = $this->unallocatedFormatter->format($sheet, $locale);

        if (empty($unallocated)) {
            return $planning;
        }

        // In this case, the planning has already a new line at the end
        return $planning . $unallocated;
    }
}
