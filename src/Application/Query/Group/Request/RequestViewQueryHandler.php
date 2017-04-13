<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Group\Request;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\View\Group\Request\RequestView;
use Proximum\Vimeet\Domain\Model\Participant;

class RequestViewQueryHandler
{
    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesser;

    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /**
     * @param SheetInfoGuesserCache       $sheetInfoGuesser
     * @param ParticipantViewQueryHandler $participantViewQueryHandler
     */
    public function __construct(
        SheetInfoGuesserCache $sheetInfoGuesser,
        ParticipantViewQueryHandler $participantViewQueryHandler
    ) {
        $this->sheetInfoGuesser            = $sheetInfoGuesser;
        $this->participantViewQueryHandler = $participantViewQueryHandler;
    }

    /**
     * @param RequestViewQuery $query
     *
     * @return RequestView
     */
    public function handle(RequestViewQuery $query)
    {
        $sheetMet = $query->request->getSheetMet($query->sheet);

        return new RequestView(
            $query->request->getId(),
            $sheetMet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($sheetMet, $query->locale),
            $query->request->getState(),
            $query->request->isSender($sheetMet) ? RequestView::TYPE_REQUEST : RequestView::TYPE_PROPOSITION,
            array_map(function (Participant $participant) use ($query) {
                return $this->participantViewQueryHandler->handle(
                    new ParticipantViewQuery($participant, $query->locale)
                );
            }, $query->request->getParticipantsOfSheetInRequest($sheetMet)),
            $query->request->hasMeeting()
        );
    }
}
