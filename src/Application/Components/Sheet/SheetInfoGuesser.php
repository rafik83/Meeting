<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetInfoGuesser
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function guessOwnerInfo(Sheet $sheet)
    {
        $participants = $sheet->getParticipants();

        foreach ($participants as $participant) {
            if ($participant->isOwner()) {
                return $this->participantInfoGuesser->guessParticipantInfo($participant);
            }
        }

        return '';
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function guessSheetInfo(Sheet $sheet)
    {
        $sheetTemplate = $sheet->getTypeSheetTemplate();
        $sheetData     = $sheet->getData();

        foreach ($sheetTemplate as $blockKey => $block) {
            if (isset($block['template'])){
                foreach ($block['template'] as $templateKey => $template) {
                    if (isset($template['type'])) {
                        if ($template['type'] === 'lib_organisation') {
                            if (isset($sheetData[$blockKey][$templateKey])) {
                                return $sheetData[$blockKey][$templateKey];
                            }
                        }
                    }
                }
            }
        }

        $ownerInfo = $this->guessOwnerInfo($sheet);

        if (empty($ownerInfo)) {
            return sprintf('#%s', $sheet->getId());
        } else {
            return $ownerInfo;
        }
    }
}
