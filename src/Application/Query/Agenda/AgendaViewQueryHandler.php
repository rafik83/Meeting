<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class AgendaViewQueryHandler
{

    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;

    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var DayViewQueryHandler
     */
    private $dayViewQueryHandler;

    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @param SheetGuesser                              $sheetGuesser
     * @param DayRepositoryInterface                    $dayRepository
     * @param DayViewQueryHandler                       $dayViewQueryHandler
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     */
    public function __construct(
        SheetGuesser $sheetGuesser,
        DayRepositoryInterface $dayRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository
    ) {
        $this->sheetGuesser                     = $sheetGuesser;
        $this->dayRepository                    = $dayRepository;
        $this->dayViewQueryHandler              = $dayViewQueryHandler;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
    }

    /**
     * @param AgendaViewQuery $query
     *
     * @return AgendaView
     * @throws \Exception
     */
    public function handle(AgendaViewQuery $query)
    {
        $eventDays = $this->dayRepository->findByEvent($query->event);

        if (empty($eventDays)) {
            return new AgendaView([]);
        }

        $sheet       = $this->sheetGuesser->getUserSheet($query->user, $query->event, $query->locale);
        $participant = $sheet->getUserParticipant($query->user);

        if (!$participant instanceof Participant) {
            throw new \Exception('Participant not found');
        }

        $happeningParticipations = $this->happeningParticipationRepository->findByParticipant($participant);
        $unavailabilites         = $this->unavailabilityRepository->findByParticipant($participant);

        $dayViews = [];

        foreach ($eventDays as $day) {
            $dayViews[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $day,
                    $query->locale,
                    $happeningParticipations,
                    $unavailabilites
                )
            );
        }

        return new AgendaView($dayViews);
    }
}
