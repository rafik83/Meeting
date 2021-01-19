<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class Delete
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
