<?php

namespace Proximum\Vimeet\Application\Query\Spot\Agenda;

use Proximum\Vimeet\Domain\Model\Spot;

class SpotViewQuery
{
    /**
     * @var Spot
     */
    public $spot;

    /**
     * SpotViewQuery constructor.
     *
     * @param Spot $spot
     */
    public function __construct(Spot $spot)
    {
        $this->spot = $spot;
    }
}
