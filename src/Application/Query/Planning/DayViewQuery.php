<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning;

use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Unavailability;

class DayViewQuery
{
    /**
     * @var Mass[]
     */
    public $masses;

    /**
     * @var Meeting[]
     */
    public $meetings;

    /**
     * @var HappeningParticipation[]
     */
    public $happenings;

    /**
     * @var Unavailability[]
     */
    public $unavailabilities;

    /**
     * @var MassAssignment[]
     */
    public $assignments;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Day
     */
    public $day;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Sheet                    $sheet
     * @param Day                      $day
     * @param string                   $locale
     * @param Unavailability[]         $unavailabilities
     * @param HappeningParticipation[] $happenings
     * @param Mass[]                   $masses
     * @param MassAssignment[]         $assignments
     * @param Meeting[]                $meetings
     */
    public function __construct(
        Sheet $sheet,
        Day $day,
        $locale,
        array $unavailabilities,
        array $happenings,
        array $masses,
        array $assignments,
        array $meetings
    ) {
        $this->sheet            = $sheet;
        $this->day              = $day;
        $this->locale           = $locale;
        $this->unavailabilities = $unavailabilities;
        $this->happenings       = $happenings;
        $this->masses           = $masses;
        $this->assignments      = $assignments;
        $this->meetings         = $meetings;
    }
}
