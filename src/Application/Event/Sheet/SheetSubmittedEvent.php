<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class SheetSubmittedEvent extends Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var DateTimeInterface
     */
    private $datetime;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $sheetOrganization;

    /**
     * SheetSubmittedEvent constructor.
     *
     * @param Sheet             $sheet
     * @param Admin             $admin
     * @param DateTimeInterface $datetime
     * @param string            $sheetOrganization
     * @param string            $locale
     */
    public function __construct(
        Sheet $sheet,
        Admin $admin,
        DateTimeInterface $datetime,
        $sheetOrganization,
        $locale
    ) {
        $this->sheet             = $sheet;
        $this->datetime          = $datetime;
        $this->admin             = $admin;
        $this->locale            = $locale;
        $this->sheetOrganization = $sheetOrganization;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getSheetOrganization()
    {
        return $this->sheetOrganization;
    }
}
