<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature\Import;

use Proximum\Vimeet\Domain\Model\Nomenclature;

interface ImporterInterface
{
    /**
     * @param Nomenclature $nomenclature
     * @param string       $value
     * @param string       $charset
     */
    public function import(Nomenclature $nomenclature, $value, $charset);
}
