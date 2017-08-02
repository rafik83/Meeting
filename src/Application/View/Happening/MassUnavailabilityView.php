<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

use Proximum\Vimeet\Application\View\Unavailability\Mass\AbstractMassView;

class MassUnavailabilityView extends AbstractMassView
{
    const TYPE_MASS_UNAVAIBILITY = 'mass_unavaibility';

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_MASS_UNAVAIBILITY;
    }
}
