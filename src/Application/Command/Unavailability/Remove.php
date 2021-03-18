<?php

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Unavailability;

class Remove
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
