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
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingOwnSheetParticipantView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Domain\Exception\Meeting\NoSheetForUserException;
use Proximum\Vimeet\Domain\Helper\LinkedSheetsTitle;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class MeetingViewQueryHandler
{
    /** @var MeetingParticipantViewQueryHandler */
    private $participantHandler;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var VideoMeetingAccess */
    private $videoMeetingAccess;

    /** @var LinkedSheetsTitle */
    private $linkedSheetsTitle;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var \DateTimeInterface */
    private $now;

    public function __construct(
        MeetingParticipantViewQueryHandler $participantHandler,
        RuleRepositoryInterface $ruleRepository,
        VideoMeetingAccess $videoMeetingAccess,
        LinkedSheetsTitle $linkedSheetsTitle,
        ParticipantInfoGuesser $participantInfoGuesser,
        \DateTimeInterface $now
    ) {
        $this->participantHandler = $participantHandler;
        $this->ruleRepository = $ruleRepository;
        $this->videoMeetingAccess = $videoMeetingAccess;
        $this->linkedSheetsTitle = $linkedSheetsTitle;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->now = $now;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView
     * @throws NoSheetForUserException
     */
    public function handle(MeetingViewQuery $query)
    {
        $userSheet = $query->meeting->getSheetOfUser($query->user);
        $sheetMet = $query->meeting->getSheetMet($userSheet);

        $sheetMetTitles = $this->linkedSheetsTitle->getSheetMetViews($userSheet, $sheetMet);
        $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($query->currentSheet, $sheetMet);
        $participants = [];

        foreach ($query->meeting->getParticipants($sheetMet) as $participant) {
            $participants[] = $this
                ->participantHandler
                ->handle(new MeetingParticipantViewQuery($participant, $rules, $query->locale));
        }

        $meetingOwnSheetParticipantViews = [];

        if (!$userSheet->hasOnlyOneParticipant()) {
            foreach ($query->meeting->getParticipants($userSheet) as $participant) {
                $infos = $this->participantInfoGuesser->guessParticipantInfos($participant, $query->locale);
                $meetingOwnSheetParticipantViews[] = new MeetingOwnSheetParticipantView(
                    $infos[Tag::PARTICIPANT_FIRSTNAME] ?? '',
                    $infos[Tag::PARTICIPANT_LASTNAME] ?? ''
                );
            }
        }

        $timeRemainingInSeconds = $query->meeting->getSlot()->getEnd()->getTimestamp() - $this->now->getTimestamp();
        $timeRemainingInSeconds = $timeRemainingInSeconds > 0 ? $timeRemainingInSeconds : 0;

        $meeting = new MeetingView(
            $query->meeting->getId(),
            $userSheet->getTitle(),
            $sheetMet->getId(),
            $sheetMetTitles,
            $meetingOwnSheetParticipantViews,
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSlot()->getEnd(),
            $timeRemainingInSeconds,
            round($timeRemainingInSeconds * 0.2),
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
