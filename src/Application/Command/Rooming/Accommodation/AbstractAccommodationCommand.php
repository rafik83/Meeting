<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Command;

abstract class AbstractAccommodationCommand implements Command
{
    /** @var string */
    public $title;

    /** @var AccommodationOvernightCapacityView[] */
    public $overnightCapacities = [];

    public function hasDuplicatedDay(): bool
    {
        $days = [];

        foreach($this->overnightCapacities as $overnightCapacity) {
            $formattedDay = $overnightCapacity->date->format('d-m-Y');

            if (isset($days[$formattedDay])) {
                return true;
            }

            $days[$formattedDay] = $formattedDay;
        }

        return false;
    }
}
