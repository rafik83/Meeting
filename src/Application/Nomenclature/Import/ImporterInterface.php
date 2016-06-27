<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature\Import;

use Proximum\Vimeet\Domain\Model\Nomenclature;

interface ImporterInterface
{
    /**
     * @param Nomenclature $nomenclature
     * @param mixed        $value
     */
    public function import(Nomenclature $nomenclature, $value);
}
