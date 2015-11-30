<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Meeting;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;
use Proximum\Vimeet\Domain\View\ParticipantNameView;

class RequestViewsBuilder
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
     * @var RequestView[]
     */
    private $requestViews;

    /**
     * @param SheetInfoGuesser       $sheetInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(SheetInfoGuesser $sheetInfoGuesser, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->requestViews           = [];
    }

    /**
     * @param Request[] $requests
     *
     * @return RequestView[]
     */
    public function generate($requests)
    {
        foreach ($requests as $request) {
            $sheetNameFrom = $this->sheetInfoGuesser->guessSheetInfo($request->getFrom());
            $sheetNameTo   = $this->sheetInfoGuesser->guessSheetInfo($request->getTo());

            $requestView = new RequestView($sheetNameFrom, $sheetNameTo, $request->getState(), $request->getCreatedAt());

            foreach ($request->getFromParticipants() as $participant) {
                $requestView->fromParticipants[] = new ParticipantNameView($this->participantInfoGuesser->guessParticipantInfo($participant));
            }

            foreach ($request->getToParticipants() as $participant) {
                $requestView->toParticipants[] = new ParticipantNameView($this->participantInfoGuesser->guessParticipantInfo($participant));
            }

            $this->requestViews[] = $requestView;
        }

        return $this->requestViews;
    }
}
