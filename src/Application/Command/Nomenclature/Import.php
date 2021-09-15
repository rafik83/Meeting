<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class Import implements Command
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $charset;

    /**
     * Import constructor.
     *
     * @param Nomenclature $nomenclature
     * @param string       $filename
     * @param string       $charset
     */
    public function __construct(Nomenclature $nomenclature, $filename, $charset)
    {
        $this->nomenclature = $nomenclature;
        $this->filename     = $filename;
        $this->charset      = $charset;
    }
}
