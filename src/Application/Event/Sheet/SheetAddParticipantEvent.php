<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher;

class SheetAddParticipantEvent extends EventDispatcher\Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Participant
     */
    private $guest;

    /**
     * SheetAddParticipantEvent constructor.
     *
     * @param Sheet       $sheet
     * @param Participant $guest
     * @param User        $user
     */
    public function __construct(Sheet $sheet, Participant $guest, User $user)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
        $this->guest = $guest;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Participant
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
