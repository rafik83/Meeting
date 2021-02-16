<?php

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
