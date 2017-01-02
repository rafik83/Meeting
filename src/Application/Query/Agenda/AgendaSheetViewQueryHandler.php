<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\AgendaSheetView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AgendaSheetViewQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var AgendaParticipantViewQueryHandler
     */
    private $agendaParticipantViewQueryHandler;

    /**
     * AgendaSheetViewQueryHandler constructor.
     *
     * @param SheetRepositoryInterface          $sheetRepository
     * @param AgendaParticipantViewQueryHandler $agendaParticipantViewQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AgendaParticipantViewQueryHandler $agendaParticipantViewQueryHandler
    ) {
        $this->sheetRepository                   = $sheetRepository;
        $this->agendaParticipantViewQueryHandler = $agendaParticipantViewQueryHandler;
    }

    public function handle(AgendaSheetViewQuery $agendaSheetViewQuery)
    {
        $sheet = $this->sheetRepository->getSheetById($agendaSheetViewQuery->sheetId);

        $participants = [];

        foreach ($sheet->getParticipants() as $participant) {

            $participants[] = $this->agendaParticipantViewQueryHandler->handle(
                new AgendaParticipantViewQuery(
                    $participant,
                    $sheet->getEvent(),
                    $sheet,
                    $agendaSheetViewQuery->locale
                )
            );
        }

        return new AgendaSheetView($participants);
    }
}
