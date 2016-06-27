<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature\Export;

use Proximum\Vimeet\Domain\Model\Nomenclature;

interface ExporterInterface
{
    /**
     * @param Nomenclature $nomenclature
     * @param mixed        $output
     *
     * @return \SplFileInfo
     */
    public function export(Nomenclature $nomenclature, $output);
}
