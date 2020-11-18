<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Security\VideoMeetingAccess;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingOwnSheetParticipantView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Domain\Exception\Meeting\NoSheetForUserException;
use Proximum\Vimeet\Domain\Helper\LinkedSheetsTitle;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class MeetingViewQueryHandler
{
    /** @var MeetingParticipantViewQueryHandler */
    private $participantHandler;

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
        VideoMeetingAccess $videoMeetingAccess,
        LinkedSheetsTitle $linkedSheetsTitle,
        ParticipantInfoGuesser $participantInfoGuesser,
        \DateTimeInterface $now
    ) {
        $this->participantHandler = $participantHandler;
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
    public function handle(MeetingViewQuery $query): MeetingView
    {
        $userSheet = $query->meeting->getSheetOfUser($query->user);
        $sheetMet = $query->meeting->getSheetMet($userSheet);

        $sheetMetTitles = $this->linkedSheetsTitle->getSheetMetViews($userSheet, $sheetMet);
        $rules = [
            new Rule(
                $query->event,
                $query->currentSheet->getType(),
                $sheetMet->getType(),
                Tag::getAll()
            )
        ];
        $participants = [];
        $participantInfosByUserId = [];

        $sheetMetTitlesImplode = implode(', ', array_map(static function ($sheetTitle) {
            return $sheetTitle->getTitle();
        }, $sheetMetTitles));

        foreach ($query->meeting->getParticipants($sheetMet) as $participant) {
            $userId = $participant->getUser()->getId();
            $meetingParticipantView = $this
                ->participantHandler
                ->handle(new MeetingParticipantViewQuery($participant, $rules, $query->locale));

            $participants[] = $meetingParticipantView;

            $participantPosition = !empty($meetingParticipantView->card->position) ? '(' . $meetingParticipantView->card->position . ')' : '';
            $participantInfosByUserId[$userId] = sprintf(
                '%s %s %s - %s',
                $meetingParticipantView->card->firstname,
                $meetingParticipantView->card->lastname,
                $participantPosition,
                $sheetMetTitlesImplode
             );
        }

        $meetingOwnSheetParticipantViews = [];
        $userParticipantId = null;

        foreach ($query->meeting->getParticipants($userSheet) as $participant) {
            $userId = $participant->getUser()->getId();
            $infos = $this->participantInfoGuesser->guessParticipantInfos($participant, $query->locale);
            $meetingOwnSheetParticipantView = new MeetingOwnSheetParticipantView(
                $infos[Tag::PARTICIPANT_FIRSTNAME] ?? '',
                $infos[Tag::PARTICIPANT_LASTNAME] ?? '',
                $infos[Tag::PARTICIPANT_POSITION] ?? ''
            );

            if (!$userSheet->hasOnlyOneParticipant()) {
                $meetingOwnSheetParticipantViews[] = $meetingOwnSheetParticipantView;
            }

            $participantPosition = !empty($meetingOwnSheetParticipantView->position) ? '(' . $meetingOwnSheetParticipantView->position . ')' : '';
            $participantInfosByUserId[$userId] = sprintf(
                '%s %s %s - %s',
                $meetingOwnSheetParticipantView->firstName,
                $meetingOwnSheetParticipantView->lastName,
                $participantPosition,
                $userSheet->getTitle()
            );
            if ($userId === $query->user->getId()) {
                $userParticipantId = $participant->getId();
            }
        }

        if (null === $userParticipantId) {
            $userParticipantId = $userSheet->getUserParticipant($query->user)->getId();
        }

        $timeRemainingInSeconds = max(
            0,
            $query->meeting->getSlot()->getEnd()->getTimestamp() - $this->now->getTimestamp()
        );

        return new MeetingView(
            $query->meeting->getId(),
            $userSheet->getId(),
            $userParticipantId,
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
            $participantInfosByUserId,
            $query->isUserParticipantMultipleSheets,
            $query->meeting->getSpot()->isVisio(),
            $this->videoMeetingAccess->allowedToAccess($query->meeting)
        );
    }
}
