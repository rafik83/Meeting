<?php

namespace Proximum\Vimeet\Application\Nomenclature\Export;

use Proximum\Vimeet\Domain\Model\Nomenclature;

interface ExporterInterface
{
    /**
     * @param Nomenclature $nomenclature
     * @param string       $output
     * @param string       $charset
     *
     * @return \SplFileInfo
     */
    public function export(Nomenclature $nomenclature, $output, $charset);
}
