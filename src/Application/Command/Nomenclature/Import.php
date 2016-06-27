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

class Import
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var string
     */
    public $filename;

    /**
     * Import constructor.
     *
     * @param Nomenclature $nomenclature
     * @param string       $filename
     */
    public function __construct(Nomenclature $nomenclature, $filename)
    {
        $this->nomenclature = $nomenclature;
        $this->filename     = $filename;
    }
}
