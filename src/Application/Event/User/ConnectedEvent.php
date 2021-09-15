<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class ConnectedEvent extends Event
{
    private Sheet $sheet;
    private User $user;

    public function __construct(Sheet $sheet, User $user)
    {
        $this->sheet = $sheet;
        $this->user = $user;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
