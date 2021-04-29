<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class Delete implements Command
{
    /**
     * @var Mass
     */
    public $mass;

    /**
     * @param Mass $mass
     */
    public function __construct(Mass $mass)
    {
        $this->mass = $mass;
    }
}
