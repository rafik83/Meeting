<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Domain\Model\Happening;

class HappeningViewQuery
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
