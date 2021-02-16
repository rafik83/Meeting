<?php

namespace Proximum\Vimeet\Application\Event\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\EventDispatcher;

class PreRegisterEvent extends EventDispatcher\Event
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
     * Array of Tag => String for participant and sheet data
     *
     * @var array
     */
    private $participantData;

    /**
     * PreRegisterEvent constructor.
     *
     * @param ParticipantInfoGuesser $participantInfoGuesser
     * @param Event                  $event
     * @param User                   $user
     * @param string                 $locale
     * @param Participant            $participant
     * @param Sheet                  $sheet
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        $event,
        $user,
        $locale,
        Participant $participant,
        Sheet $sheet
    ) {
        $this->event                  = $event;
        $this->user                   = $user;
        $this->locale                 = $locale;
        $this->participant            = $participant;
        $this->sheet                  = $sheet;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->participantData        = $this->participantInfoGuesser->guessParticipantInfoForMail($participant, $locale);
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

    /**
     * @return array
     */
    public function getParticipantData()
    {
        return $this->participantData;
    }
}
