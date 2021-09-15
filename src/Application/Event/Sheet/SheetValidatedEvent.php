<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class SheetValidatedEvent extends Event
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
     * @var string
     */
    private $comment;

    /**
     * SheetValidatedEvent constructor.
     *
     * @param Sheet             $sheet
     * @param DateTimeInterface $date
     * @param string            $comment
     * @param AbstractUser      $user
     */
    public function __construct(Sheet $sheet, DateTimeInterface $date, $comment, AbstractUser $user = null)
    {
        $this->sheet   = $sheet;
        $this->user    = $user;
        $this->date    = $date;
        $this->comment = $comment;
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

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
