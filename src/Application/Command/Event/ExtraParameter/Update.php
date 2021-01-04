<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;

class Update
{
    /** @var ExtraParameter */
    public $extraParameter;

    /** @var string */
    public $name;

    /** @var string */
    public $value;

    /**
     * @param ExtraParameter $extraParameter
     */
    public function __construct(ExtraParameter $extraParameter)
    {
        $this->extraParameter = $extraParameter;
        $this->name = $extraParameter->getName();
        $this->value = $extraParameter->getValue();
    }
}
