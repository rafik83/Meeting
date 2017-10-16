<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Command;

use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoSheetInfoGuesser;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class VianeoGetSheetDataHandler
{
    /** @var VianeoSheetInfoGuesser */
    private $vianeoSheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /**
     * @param VianeoSheetInfoGuesser $vianeoSheetInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(
        VianeoSheetInfoGuesser $vianeoSheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->vianeoSheetInfoGuesser = $vianeoSheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param VianeoGetSheetData $vianeoGetSheetData
     */
    public function handle(VianeoGetSheetData $vianeoGetSheetData)
    {
        $sheet = $vianeoGetSheetData->sheet;
        $locale = $vianeoGetSheetData->locale;

        $participantData = $this->participantInfoGuesser->guessParticipantInfos($sheet->getFirstParticipant(), $locale);
        dump($participantData);

        $sheetData = $this->vianeoSheetInfoGuesser->handle($sheet, $locale);
        dump($sheetData);
    }
}
