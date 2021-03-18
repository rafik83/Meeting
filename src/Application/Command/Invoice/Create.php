<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Sheet;

class Create
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Prefix
     */
    public $prefix;

    /**
     * @param Sheet  $sheet
     * @param Prefix $prefix
     */
    public function __construct(Sheet $sheet, Prefix $prefix)
    {
        $this->sheet  = $sheet;
        $this->prefix = $prefix;
    }
}
