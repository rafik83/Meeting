<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Unavailability;

class Remove
{
    /**
     * @var Unavailability
     */
    public $unavailability;

    /**
     * Remove constructor.
     *
     * @param Unavailability $unavailability
     */
    public function __construct(Unavailability $unavailability)
    {
        $this->unavailability = $unavailability;
    }
}
