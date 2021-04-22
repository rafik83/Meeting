<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class Update implements Command
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $sort;

    /**
     * Update constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
        $this->title        = $nomenclature->getTitle();
        $this->sort         = $nomenclature->isSorted();
    }
}
