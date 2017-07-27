<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Meeting\VideoConferenceViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\VideoConferenceViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class MeetingViewQueryHandler
{
    /**
     * @var MeetingParticipantViewQueryHandler
     */
    private $participantHandler;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param MeetingParticipantViewQueryHandler $participantHandler
     * @param RuleRepositoryInterface            $ruleRepository
     */
    public function __construct(
        MeetingParticipantViewQueryHandler $participantHandler,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->participantHandler = $participantHandler;
        $this->ruleRepository     = $ruleRepository;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $query)
    {
        $userSheet    = $query->meeting->getSheetOfUser($query->user);
        $sheetMet     = $query->meeting->getSheetMet($userSheet);
        $rules        = $this
            ->ruleRepository
            ->getBySeerTypeAndSeeableType($query->currentSheet->getType(), $sheetMet->getType());
        $participants = [];

        foreach ($query->meeting->getParticipants($sheetMet) as $participant) {
            $participants[] = $this
                ->participantHandler
                ->handle(new MeetingParticipantViewQuery($participant, $rules, $query->locale));
        }

        $isSheetDetailsSeeAble = !empty($rules);

        $meeting = new MeetingView(
            $query->meeting->getId(),
            $userSheet->getTitle(),
            $sheetMet->getId(),
            $sheetMet->getTitle(),
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSlot()->getEnd(),
            $query->meeting->getSpot()->getReference(),
            $query->event->getTimeZone(),
            $query->event->getConfiguration()->getLeftColor(),
            $query->event->getConfiguration()->getRightColor(),
            $participants,
            $isSheetDetailsSeeAble,
            $query->isUserParticipantMultipleSheets,
            $query->meeting->getSpot()->isVisio(),
            $videoConferenceView ?? null
        );

        return $meeting;
    }
}
