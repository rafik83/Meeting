<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class MassViewQuery
{
    /** @var Mass */
    public $mass;

    /** @var string */
    public $locale;

    /**
     * @param Mass   $mass
     * @param string $locale
     */
    public function __construct(Mass $mass, $locale)
    {
        $this->mass   = $mass;
        $this->locale = $locale;
    }
}
