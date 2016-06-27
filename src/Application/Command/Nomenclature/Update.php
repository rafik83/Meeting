<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;

class Update
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var bool
     */
    public $sort;

    /**
     * Update constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
        $this->sort         = $nomenclature->isSorted();
    }
}
