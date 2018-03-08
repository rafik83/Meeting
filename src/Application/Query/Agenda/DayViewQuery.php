<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;

class DayViewQuery
{
    /** @var Day */
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

    /**
     * @param Day $day
     * @param Sheet $currentSheet
     * @param Event $event
     * @param Participant $participant
     * @param User $userViewing
     * @param bool $isUserParticipantMultipleSheet
     * @param string $locale
     * @param HappeningParticipation[] $happenings
     * @param Unavailability[] $unavailabilities
     * @param Mass[] $masses
     * @param Meeting[] $meetings
     */
    public function __construct(
        Day $day,
        Sheet $currentSheet,
        Event $event,
        Participant $participant,
        User $userViewing,
        $isUserParticipantMultipleSheet,
        $locale,
        array $happenings = [],
        array $unavailabilities = [],
        array $masses = [],
        array $meetings = []
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
    }

    /**
     * @return bool
     */
    public function isParticipantUserViewing(): bool
    {
        return $this->participant->getUser() === $this->userViewing;
    }
}
