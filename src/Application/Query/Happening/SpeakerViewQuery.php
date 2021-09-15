<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class SpeakerViewQuery
{
    /** @var Happening */
    public $happening;

    /** @var string */
    public $locale;

    public function __construct(Happening $happening, string $locale)
    {
        $this->happening = $happening;
        $this->locale = $locale;
    }
}
