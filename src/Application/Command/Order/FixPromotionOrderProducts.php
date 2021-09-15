<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;

class FixPromotionOrderProducts implements Command
{
    /** @var bool */
    public $dry;

    public function __construct(bool $dry)
    {
        $this->dry = $dry;
    }
}
