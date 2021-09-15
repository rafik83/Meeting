<?php

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Type;

class Remove implements Command
{
    /**
     * @var Type
     */
    public $type;

    /**
     * Remove constructor.
     *
     * @param Type $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
    }
}
