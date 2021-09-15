<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class SheetEnableDisableEvent extends Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var AbstractUser
     */
    private $user;

    /**
     * @var DateTimeInterface
     */
    private $date;

    /**
     * @var bool
     */
    private $state;

    /**
     * @param Sheet             $sheet
     * @param AbstractUser      $user
     * @param DateTimeInterface $date
     * @param bool              $state
     */
    public function __construct(Sheet $sheet, AbstractUser $user, DateTimeInterface $date, $state)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
        $this->date  = $date;
        $this->state = $state;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return AbstractUser
     */
    public function getAuthor()
    {
        return $this->user;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return bool
     */
    public function getState()
    {
        return $this->state;
    }
}
