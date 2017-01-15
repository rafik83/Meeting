<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class MeetingViewQueryHandler
{
    /**
     * @var ParticipantViewQueryHandler
     */
    private $participantHandler;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param ParticipantViewQueryHandler $participantHandler
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param RuleRepositoryInterface     $ruleRepository
     */
    public function __construct(
        ParticipantViewQueryHandler $participantHandler,
        SheetInfoGuesser $sheetInfoGuesser,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->participantHandler = $participantHandler;
        $this->sheetInfoGuesser   = $sheetInfoGuesser;
        $this->ruleRepository     = $ruleRepository;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $query)
    {
        $sheetMet     = $query->meeting->getSheetMet($query->currentSheet);
        $rules        = $this
            ->ruleRepository
            ->getBySeerTypeAndSeeableType($query->currentSheet->getType(), $sheetMet->getType());
        $participants = [];

        if ($sheetMet === $query->meeting->getFromSheet()) {
            foreach ($query->meeting->getFromParticipants()->toArray() as $participant) {
                $participants[] = $this
                    ->participantHandler
                    ->handle(new ParticipantViewQuery($participant, $rules, $query->locale));
            }
        } elseif ($sheetMet === $query->meeting->getToSheet()) {
            foreach ($query->meeting->getToParticipants()->toArray() as $participant) {
                $participants[] = $this
                    ->participantHandler
                    ->handle(new ParticipantViewQuery($participant, $rules, $query->locale));
            }
        }

        $meeting  = new MeetingView(
            $sheetMet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($sheetMet, $query->locale),
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSlot()->getEnd(),
            $query->meeting->getSpot()->getReference(),
            $query->event->getTimeZone(),
            $query->event->getConfiguration()->getLeftColor(),
            $query->event->getConfiguration()->getRightColor(),
            $participants
        );

        return $meeting;
    }
}
