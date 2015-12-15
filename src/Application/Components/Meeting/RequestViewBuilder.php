<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;
use Proximum\Vimeet\Domain\View\ParticipantNameView;

class RequestViewBuilder
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param SheetInfoGuesser       $sheetInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(SheetInfoGuesser $sheetInfoGuesser, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Request $request
     *
     * @return RequestView
     */
    public function generate(Request $request)
    {
        $sheetNameFrom = $this->sheetInfoGuesser->guessSheetInfo($request->getFromSheet());
        $sheetNameTo   = $this->sheetInfoGuesser->guessSheetInfo($request->getToSheet());

        $requestView = new RequestView(
            $request->getId(),
            $sheetNameFrom,
            $sheetNameTo,
            $request->getState(),
            $request->getDescription(),
            $request->getCreatedAt(),
            ''
        );

        foreach ($request->getFromParticipants() as $participant) {
            $participantInfo                 = $this->participantInfoGuesser->guessParticipantInfo($participant);
            $requestView->fromParticipants[] = new ParticipantNameView($participantInfo);
        }

        foreach ($request->getToParticipants() as $participant) {
            $participantInfo               = $this->participantInfoGuesser->guessParticipantInfo($participant);
            $requestView->toParticipants[] = new ParticipantNameView($participantInfo);
        }

        return $requestView;
    }
}
