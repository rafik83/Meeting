<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class DayViewQuery
{
    /** @var TimeRangeView */
    public $day;

    /** @var string */
    public $locale;

    /** @var HappeningParticipation[] */
    public $happenings;

    /** @var Participant */
    public $participant;

    /** @var User */
    public $userViewing;

    /** @var Unavailability[] */
    public $unavailabilities;

    /** @var Mass[] */
    public $masses;

    /** @var Meeting[] */
    public $meetings;

    /** @var Sheet */
    public $currentSheet;

    /** @var Event */
    public $event;

    /** @var bool */
    public $isUserParticipantMultipleSheet;

    /** @var array|MeetingSlot[] */
    public $meetingSlots;

    /**
     * @param HappeningParticipation[] $happenings
     * @param Unavailability[]         $unavailabilities
     * @param Mass[]                   $masses
     * @param Meeting[]                $meetings
     * @param MeetingSlot[]            $meetingSlots
     */
    public function __construct(
        TimeRangeView $day,
        Sheet $currentSheet,
        Event $event,
        Participant $participant,
        User $userViewing,
        bool $isUserParticipantMultipleSheet,
        string $locale,
        array $happenings = [],
        array $unavailabilities = [],
        array $masses = [],
        array $meetings = [],
        array $meetingSlots = []
    ) {
        $this->day = $day;
        $this->currentSheet = $currentSheet;
        $this->event = $event;
        $this->participant = $participant;
        $this->userViewing = $userViewing;
        $this->isUserParticipantMultipleSheet = $isUserParticipantMultipleSheet;
        $this->locale = $locale;
        $this->happenings = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses = $masses;
        $this->meetings = $meetings;
        $this->meetingSlots = $meetingSlots;
    }

    /**
     * @return bool
     */
    public function isParticipantUserViewing(): bool
    {
        return $this->participant->getUser() === $this->userViewing;
    }
}
