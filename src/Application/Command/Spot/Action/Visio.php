<?php

namespace Proximum\Vimeet\Application\Command\Spot\Action;

use Proximum\Vimeet\Domain\Model\Spot;

class Visio
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
