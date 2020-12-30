<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;

class CreateResult
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * CreateResult constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }
}
