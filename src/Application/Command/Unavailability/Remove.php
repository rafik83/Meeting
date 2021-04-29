<?php

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Unavailability;

class Remove implements Command
{
    /**
     * @var Unavailability
     */
    public $unavailability;

    /**
     * Remove constructor.
     *
     * @param Unavailability $unavailability
     */
    public function __construct(Unavailability $unavailability)
    {
        $this->unavailability = $unavailability;
    }
}
