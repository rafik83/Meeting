<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class SheetChangedTypeEvent extends Event
{
    /** @var Sheet */
    private $sheet;

    /** @var Admin */
    private $user;

    /** @var DateTimeInterface */
    private $date;

    /** @var string */
    private $comment;

    /** @var string */
    private $fromTypeTitle;

    /** @var string */
    private $locale;

    /**
     * @param Sheet             $sheet
     * @param Admin             $user
     * @param DateTimeInterface $date
     * @param string            $comment
     * @param string            $fromTypeTitle
     * @param string            $locale
     */
    public function __construct(
        Sheet $sheet,
        Admin $user,
        DateTimeInterface $date,
        $comment,
        $fromTypeTitle,
        $locale
    ) {
        $this->sheet         = $sheet;
        $this->user          = $user;
        $this->date          = $date;
        $this->comment       = $comment;
        $this->fromTypeTitle = $fromTypeTitle;
        $this->locale        = $locale;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Admin
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

    /**
     * @return string
     */
    public function getFromTypeTitle()
    {
        return $this->fromTypeTitle;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
