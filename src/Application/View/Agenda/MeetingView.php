<?php

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingOwnSheetParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingParticipantView;

class MeetingView extends AbstractTimeEntityView
{
    /** @var int */
    public $id;

    /** @var int */
    public $userSheetId;

    /** @var int */
    public $userParticipantId;

    /** @var string */
    public $userSheetTitle;

    /** @var int */
    public $sheetMetId;

    /** @var string */
    public $spotRef;

    /** @var MeetingParticipantView[] */
    public $participants;

    /** @var SheetMetView[] */
    public $sheetMetTitle;

    /** @var int */
    public $timeRemainingInSeconds;

    /** @var int */
    public $warningTimeRemainingInSeconds;

    /** @var string */
    public $timeZone;

    /** @var string */
    public $leftColor;

    /** @var string */
    public $rightColor;

    /** @var bool */
    public $isUserParticipantMultipleSheets;

    /** @var MeetingOwnSheetParticipantView[] */
    public $meetingOwnSheetParticipantViews;

    /** @var bool */
    private $isVisio;

    /** @var bool */
    private $isVisioAvailable;

    /** @var array */
    public $participantInfosByUserId;

    /**
     * @param SheetMetView[]                   $sheetMetTitle
     * @param MeetingOwnSheetParticipantView[] $meetingOwnSheetParticipantViews
     * @param MeetingParticipantView[]         $participants
     */
    public function __construct(
        int $id,
        int $userSheetId,
        int $userParticipantId,
        string $userSheetTitle,
        $sheetMetId,
        array $sheetMetTitle,
        array $meetingOwnSheetParticipantViews,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        int $timeRemainingInSeconds,
        int $warningTimeRemainingInSeconds,
        string $spotRef,
        string $timeZone,
        string $leftColor,
        string $rightColor,
        array $participants,
        array $participantInfosByUserId = [],
        bool $isUserParticipantMultipleSheets = false,
        bool $isVisio = false,
        bool $isVisioAvailable = false
    ) {
        $this->id = $id;
        $this->userSheetId = $userSheetId;
        $this->userParticipantId = $userParticipantId;
        $this->userSheetTitle = $userSheetTitle;
        $this->sheetMetId = $sheetMetId;
        $this->sheetMetTitle = $sheetMetTitle;
        $this->meetingOwnSheetParticipantViews = $meetingOwnSheetParticipantViews;
        $this->spotRef = $spotRef;
        $this->begin = $begin;
        $this->end = $end;
        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->warningTimeRemainingInSeconds = $warningTimeRemainingInSeconds;
        $this->timeZone = $timeZone;
        $this->leftColor = $leftColor;
        $this->rightColor = $rightColor;
        $this->participants = $participants;
        $this->isUserParticipantMultipleSheets = $isUserParticipantMultipleSheets;
        $this->isVisio = $isVisio;
        $this->isVisioAvailable = $isVisioAvailable;
        $this->participantInfosByUserId = $participantInfosByUserId;
    }

    public function getDuration(): \DateInterval
    {
        return $this->end->diff($this->begin);
    }

    public function isVisio(): bool
    {
        return $this->isVisio;
    }

    public function isVisioAvailable(): bool
    {
        return $this->isVisioAvailable;
    }

    public function isVisioAndAvailable(): bool
    {
        return $this->isVisio && $this->isVisioAvailable;
    }

    public function getMeetingOwnSheetParticipantNames(): string
    {
        return implode(', ', $this->meetingOwnSheetParticipantViews);
    }

    public function hasMeetingOwnSheetParticipantNames(): bool
    {
        return !empty($this->meetingOwnSheetParticipantViews);
    }

    public function getSheetMetTitles(): string
    {
        return implode(', ', array_map(static function ($sheetTitle) {
            return $sheetTitle->getTitle();
        }, $this->sheetMetTitle));
    }
}
