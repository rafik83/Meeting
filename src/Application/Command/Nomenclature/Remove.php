<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class Remove implements Command
{
    /** @var Nomenclature */
    public $nomenclature;

    /**
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }
}
