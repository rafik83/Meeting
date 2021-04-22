<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Package;

class Duplicate implements Command
{
    /** @var string */
    public $title;

    /** @var Package */
    public $package;

    /**
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }
}
