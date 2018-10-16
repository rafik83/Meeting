<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class FilterMassUnavailabilitiesBySheetsType
{
    /**
     * @param Mass[]  $masses
     * @param Sheet[] $sheets
     *
     * @return Mass[]
     */
    public function handle(array $masses, array $sheets): array
    {
        $filteredMasses = [];

        foreach ($masses as $massUnavailability) {
            foreach ($sheets as $sheet) {
                if ($massUnavailability->hasType($sheet->getType())) {
                    $filteredMasses[] = $massUnavailability;
                    break;
                }
            }
        }

        return $filteredMasses;
    }
}
