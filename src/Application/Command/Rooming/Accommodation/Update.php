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
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;

class Update implements Command
{
    /** @var string */
    public $title;

    /** @var AccommodationOvernightCapacityView[] */
    public $overnightCapacities;

    /** @var Accommodation */
    public $accommodation;

    public function __construct(
        Accommodation $accommodation
    ) {
        $this->accommodation = $accommodation;
        $this->title = $accommodation->getTitle();

        foreach ($accommodation->getOvernightCapacities() as $overnightCapacity) {
            $this->overnightCapacities[] = new AccommodationOvernightCapacityView(
                $overnightCapacity->getDate(),
                $overnightCapacity->getCapacity()
            );
        }
    }
}
