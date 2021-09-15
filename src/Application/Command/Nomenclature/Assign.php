<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class Assign implements Command
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var Event
     */
    public $event;

    /**
     * Assign constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }
}
