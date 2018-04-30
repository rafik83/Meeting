<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot\Action;

use Proximum\Vimeet\Domain\Model\Spot;

class Visio
{
    /**
     * @var Spot
     */
    public $spot;

    /**
     * @param Spot $spot
     */
    public function __construct(Spot $spot)
    {
        $this->spot = $spot;
    }
}
