<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class Assign
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var Event
     */
    public $event;

    /**
     * Assign constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }
}
