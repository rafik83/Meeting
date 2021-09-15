<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Command;

use Proximum\Vimeet\Domain\Model\Sheet;

class VianeoRegisterSheet
{
    /** @var Sheet */
    public $sheet;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
