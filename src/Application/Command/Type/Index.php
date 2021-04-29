<?php

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Type;

class Index implements Command
{
    /**
     * @var Type[]
     */
    public $types;

    /**
     * @param Type[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }
}
