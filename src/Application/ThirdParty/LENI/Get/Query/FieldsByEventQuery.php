<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query;

class FieldsByEventQuery
{
    /** @var array */
    public $typesMapping;

    /** @var array */
    public $customDataMapping;

    public function __construct(array $typesMapping, array $customDataMapping)
    {
        $this->typesMapping = $typesMapping;
        $this->customDataMapping = $customDataMapping;
    }
}
