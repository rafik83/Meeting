<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

class MeetingSheetView
{
    /**
     * @var string
     */
    public $sheetName;

    /**
     * @var string
     */
    public $category;

    /**
     * @var string
     */
    public $turnover;

    /**
     * @var string
     */
    public $employees;

    /**
     * @var string
     */
    public $website;

    /**
     * @var string
     */
    public $address;

    /**
     * @var string
     */
    public $zipcode;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $country;

    /**
     * @var MeetingParticipantView[]
     */
    public $participants;

    /**
     * @var string
     */
    public $type;

    /**
     * MeetingSheetView constructor.
     *
     * @param string                   $category
     * @param string                   $turnover
     * @param string                   $employees
     * @param string                   $website
     * @param string                   $address
     * @param string                   $zipcode
     * @param string                   $city
     * @param string                   $country
     * @param string                   $type
     * @param MeetingParticipantView[] $participants
     */
    public function __construct(
        $category,
        $turnover,
        $employees,
        $website,
        $address,
        $zipcode,
        $city,
        $country,
        $type,
        array $participants
    ) {
        $this->participants = $participants;
        $this->category     = $category;
        $this->turnover     = $turnover;
        $this->employees    = $employees;
        $this->website      = $website;
        $this->address      = $address;
        $this->zipcode      = $zipcode;
        $this->city         = $city;
        $this->country      = $country;
        $this->type         = $type;
    }
}
