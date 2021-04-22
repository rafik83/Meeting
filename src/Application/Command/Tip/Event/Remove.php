<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Remove implements Command
{
    /** @var Tip */
    public $tip;

    /**
     * @param Tip $tip
     */
    public function __construct(Tip $tip)
    {
        $this->tip = $tip;
    }
}
