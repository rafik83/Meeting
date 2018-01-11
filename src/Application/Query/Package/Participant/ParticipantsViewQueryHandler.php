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

class ParticipantsViewQueryHandler
{
    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var ParticipantProductViewQueryHandler */
    private $participantProductViewQueryHandler;

    /**
     * @param ParticipantViewQueryHandler        $participantViewQueryHandler
     * @param ParticipantProductViewQueryHandler $participantProductViewQueryHandler
     */
    public function __construct(
        ParticipantViewQueryHandler $participantViewQueryHandler,
        ParticipantProductViewQueryHandler $participantProductViewQueryHandler
    ) {
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->participantProductViewQueryHandler = $participantProductViewQueryHandler;
    }

    /**
     * @param ParticipantsViewQuery $participantsViewQuery
     * @return ParticipantsView
     */
    public function handle(ParticipantsViewQuery $participantsViewQuery)
    {
        $locale = $participantsViewQuery->locale;
        $sheet = $participantsViewQuery->sheet;

        $participantView = [];

        foreach ($sheet->getParticipants() as $participant) {
            $participantView[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery(
                    $participant,
                    $locale
                )
            );
        }

        $participantsView = new ParticipantsView(
            $participantView,
            $this->participantProductViewQueryHandler->handle(new ParticipantProductViewQuery($sheet, $locale))
        );

        return $participantsView;
    }
}
