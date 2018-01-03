<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Application\View\Package\ParticipantsView;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;

class ParticipantsViewQueryHandler
{
    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var IncludedParticipantGuesser */
    private $includedParticipantGuesser;

    /**
     * @param ParticipantViewQueryHandler $participantViewQueryHandler
     * @param IncludedParticipantGuesser  $includedParticipantGuesser
     */
    public function __construct(
        ParticipantViewQueryHandler $participantViewQueryHandler,
        IncludedParticipantGuesser $includedParticipantGuesser
    ) {
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->includedParticipantGuesser  = $includedParticipantGuesser;
    }

    /**
     * @param ParticipantsViewQuery $participantsViewQuery
     * @return ParticipantsView
     */
    public function handle(ParticipantsViewQuery $participantsViewQuery)
    {
        $locale = $participantsViewQuery->locale;

        $includedParticipantView = $this->includedParticipantGuesser->getIncludedParticipantView(
            $participantsViewQuery->sheet
        );

        $numberIncluded = $includedParticipantView->totalQuantity;

        $participantProduct = $participantsViewQuery->sheet->getPackage()->getParticipant();

        $participantView = [];

        foreach ($participantsViewQuery->sheet->getParticipants() as $participant) {
            $participantView[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery(
                    $participantProduct,
                    $participant,
                    $locale,
                    count($participantView) < $numberIncluded
                )
            );
        }

        $participantsView = new ParticipantsView($participantView);

        return $participantsView;
    }
}
