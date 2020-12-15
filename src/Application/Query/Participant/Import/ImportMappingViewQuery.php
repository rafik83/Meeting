<?php

namespace Proximum\Vimeet\Application\Query\Participant\Import;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Type;

class ImportMappingViewQuery implements Query
{
    /** @var string */
    public $locale;

    /** @var Type */
    public $type;

    public function __construct(Type $type, string $locale)
    {
        $this->locale = $locale;
        $this->type   = $type;
    }
}
