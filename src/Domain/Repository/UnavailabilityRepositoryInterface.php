<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;

interface UnavailabilityRepositoryInterface
{
    /**
     * @param Unavailability $unavailability
     */
    public function add(Unavailability $unavailability);

    /**
     * @param Unavailability $unavailability
     */
    public function set(Unavailability $unavailability);

    /**
     * @param Unavailability $unavailability
     */
    public function remove(Unavailability $unavailability);

    /**
     * @param Schedule $schedule
     * @param Sheet    $sheet
     * @param User     $user
     *
     * @return Unavailability[]
     */
    public function findByScheduleSheetAndUser(Schedule $schedule, Sheet $sheet, User $user);

    /**
     * @param Unavailability $unavailability
     *
     * @return Unavailability[]
     */
    public function getOverlapUnavailabilities(Unavailability $unavailability);
}
