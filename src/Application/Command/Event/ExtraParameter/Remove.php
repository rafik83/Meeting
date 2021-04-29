<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;

class Remove implements Command
{
    /** @var ExtraParameter */
    public $extraParameter;

    /**
     * @param ExtraParameter $extraParameter
     */
    public function __construct(ExtraParameter $extraParameter)
    {
        $this->extraParameter = $extraParameter;
    }
}
