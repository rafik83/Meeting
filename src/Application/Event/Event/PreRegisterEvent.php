<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class PreRegisterEvent extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * PreRegisterEvent constructor.
     *
     * @param ParticipantInfoGuesser $participantInfoGuesser
     * @param                   $event
     * @param                   $user
     * @param                   $locale
     * @param Participant       $participant
     * @param Sheet             $sheet
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        $event,
        $user,
        $locale,
        Participant $participant,
        Sheet $sheet
    ) {
        $this->event             = $event;
        $this->user              = $user;
        $this->locale            = $locale;
        $this->participant       = $participant;
        $this->sheet             = $sheet;
        $this->participantInfoGuesser = $participantInfoGuesser;

        $data = $this->participantInfoGuesser->guessParticipantInfoForMail($participant, $locale);

        dump($data);die();
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

}