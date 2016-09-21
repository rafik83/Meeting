<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param Sheet             $sheet
     * @param Admin             $user
     * @param DateTimeInterface $date
     * @param string            $comment
     */
    public function __construct(Sheet $sheet, Admin $user, DateTimeInterface $date, $comment)
    {
        $this->sheet   = $sheet;
        $this->user    = $user;
        $this->date    = $date;
        $this->comment = $comment;
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
}
