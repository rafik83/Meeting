<?php

namespace Proximum\Vimeet\Application\View\Type;

class TypeWithPaymentConditionsView
{
    /** @var string */
    public $title;

    /**
     * @param string $title
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }
}
