<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;

class AssignResult
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * AssignResult constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }
}
