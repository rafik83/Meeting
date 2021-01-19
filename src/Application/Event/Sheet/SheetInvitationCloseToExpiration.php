<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class SheetInvitationCloseToExpiration extends Event
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
     * @var User
     */
    private $guest;

    /**
     * SheetInvitationCloseToExpiration constructor.
     *
     * @param Sheet $sheet
     * @param User  $user
     * @param User  $guest
     */
    public function __construct(Sheet $sheet, User $user, User $guest)
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
     * @return User
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
