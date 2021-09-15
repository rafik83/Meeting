<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class SheetViewedEvent extends Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var User
     */
    private $user;

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
