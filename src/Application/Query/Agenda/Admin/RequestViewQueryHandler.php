<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use InvalidArgumentException;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Admin\ParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class RequestViewQueryHandler
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
     * RequestViewQueryHandler constructor.
     *
     * @param SheetInfoGuesser       $sheetInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
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
            $this->sheetInfoGuesser->guessSheetTitle($sheetMet, $query->locale),
            $sheetMet->getId(),
            $this->getParticipantViews($query->request, $query->sheet, $query->locale),
            $query->request->isTransformableIntoMeeting(),
            $query->request->isOneOfSheetsNotAttend()
        );
    }

    /**
     * @param Request $request
     * @param Sheet   $sheet
     * @param string  $locale
     *
     * @return ParticipantView[]
     */
    public function getParticipantViews(Request $request, Sheet $sheet, $locale)
    {
        $participantViews = [];

        try {
            $participants = $request->getParticipants($sheet);

            foreach ($participants as $participant) {
                $participantViews[] = new ParticipantView(
                    $participant->getId(),
                    $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale)
                );
            }

            return $participantViews;
        } catch (InvalidArgumentException $exception) {
            return $participantViews;
        }
    }
}
