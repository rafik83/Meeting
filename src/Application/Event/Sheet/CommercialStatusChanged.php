<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class CommercialStatusChanged extends Event
{
    /** @var Sheet */
    private $sheet;

    /** @var Admin */
    private $admin;

    /** @var DateTimeInterface */
    private $date;

    /**
     * @param Sheet             $sheet
     * @param Admin             $admin
     * @param DateTimeInterface $date
     */
    public function __construct(Sheet $sheet, Admin $admin, DateTimeInterface $date)
    {
        $this->sheet = $sheet;
        $this->admin = $admin;
        $this->date  = $date;
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return Admin
     */
    public function getAuthor(): Admin
    {
        return $this->admin;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }
}
