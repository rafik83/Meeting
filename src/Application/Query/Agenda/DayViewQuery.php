<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;

class DayViewQuery
{
    /**
     * @var Day
     */
    public $day;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var HappeningParticipation[]
     */
    public $happenings;

    /**
     * @var Unavailability[]
     */
    public $unavailabilities;

    /**
     * @var Mass[]
     */
    public $masses;

    /**
     * @param Day                      $day
     * @param string                   $locale
     * @param HappeningParticipation[] $happenings
     * @param Unavailability[]         $unavailabilities
     * @param Mass[]                   $masses
     */
    public function __construct(
        Day $day,
        $locale,
        array $happenings = [],
        array $unavailabilities = [],
        array $masses = []
    ) {
        $this->day              = $day;
        $this->locale           = $locale;
        $this->happenings       = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses           = $masses;
    }
}
