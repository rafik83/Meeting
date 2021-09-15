<?php

namespace Proximum\Vimeet\Application\Command\Package\PromotionCode;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;

class Add implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $promotionCode;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
