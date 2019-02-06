<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Security\VideoMeetingAccess;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Domain\Exception\Meeting\NoSheetForUserException;
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
     * @var VideoMeetingAccess
     */
    private $videoMeetingAccess;

    /**
     * @param MeetingParticipantViewQueryHandler $participantHandler
     * @param RuleRepositoryInterface            $ruleRepository
     * @param VideoMeetingAccess                 $videoMeetingAccess
     */
    public function __construct(
        MeetingParticipantViewQueryHandler $participantHandler,
        RuleRepositoryInterface $ruleRepository,
        VideoMeetingAccess $videoMeetingAccess
    ) {
        $this->participantHandler = $participantHandler;
        $this->ruleRepository = $ruleRepository;
        $this->videoMeetingAccess = $videoMeetingAccess;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @throws NoSheetForUserException
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $query)
    {
        $userSheet    = $query->meeting->getSheetOfUser($query->user);
        $sheetMet     = $query->meeting->getSheetMet($userSheet);
        $rules        = $this->ruleRepository->getBySeerTypeAndSeeableType($query->currentSheet->getType(), $sheetMet->getType());
        $participants = [];

        foreach ($query->meeting->getParticipants($sheetMet) as $participant) {
            $participants[] = $this
                ->participantHandler
                ->handle(new MeetingParticipantViewQuery($participant, $rules, $query->locale));
        }

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
            $query->isUserParticipantMultipleSheets,
            $query->meeting->getSpot()->isVisio(),
            $this->videoMeetingAccess->allowedToAccess($query->meeting)
        );

        return $meeting;
    }
}
