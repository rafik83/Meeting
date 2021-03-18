<?php

namespace Proximum\Vimeet\Application\View\Type;

class TypesWithPaymentConditionsView
{
    /** @var TypeWithPaymentConditionsView[] */
    public $types;

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }
}
