<?php

namespace Proximum\Vimeet\Application\Query\Transactional\Mail\Generic;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Type;

class GenericMailViewQuery implements Query
{
    /** @var string */
    public $locale;

    /** @var string */
    public $key;

    /** @var array */
    public $data;

    /** @var Type[] */
    public $remainingTypes;

    public function __construct(
        string $locale,
        string $key,
        array $data,
        array $remainingTypes = []
    ) {
        $this->locale = $locale;
        $this->key = $key;
        $this->data = $data;
        $this->remainingTypes = $remainingTypes;
    }
}
