<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class SheetAcceptedEvent extends Event
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
     * SheetAcceptedEvent constructor.
     *
     * @param Sheet             $sheet
     * @param AbstractUser      $user
     * @param DateTimeInterface $date
     */
    public function __construct(Sheet $sheet, AbstractUser $user, DateTimeInterface $date)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
        $this->date  = $date;
    }

    /**
     * Get sheet
     *
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
}
