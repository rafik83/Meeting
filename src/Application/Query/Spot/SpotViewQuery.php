<?php

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Domain\Model\Spot;

class SpotViewQuery
{
    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Spot   $spot
     * @param string $locale
     */
    public function __construct(Spot $spot, $locale)
    {
        $this->spot   = $spot;
        $this->locale = $locale;
    }
}
