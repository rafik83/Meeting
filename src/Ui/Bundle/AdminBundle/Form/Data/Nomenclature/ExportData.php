<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;

class ExportData
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var string
     */
    public $charset;

    /**
     * ImportData constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }
}
