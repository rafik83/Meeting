<?php

namespace Proximum\Vimeet\Application\Command\Spot\Action;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Spot;

class UnVisio implements Command
{
    /**
     * @var Spot
     */
    public $spot;

    /**
     * @param Spot $spot
     */
    public function __construct(Spot $spot)
    {
        $this->spot = $spot;
    }
}
