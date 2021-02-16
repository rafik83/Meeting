<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;

class AssignSpotResult implements Command
{
    /**
     * @var int
     */
    private $sheetNumber;

    /**
     * @param int $sheetNumber
     */
    public function __construct($sheetNumber)
    {
        $this->sheetNumber = $sheetNumber;
    }

    /**
     * @return bool
     */
    public function hasInfo()
    {
        return $this->sheetNumber > 1;
    }

    /**
     * @return int
     */
    public function getSheetNumber()
    {
        return $this->sheetNumber;
    }
}
