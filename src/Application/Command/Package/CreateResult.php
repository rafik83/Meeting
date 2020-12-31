<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Package;

class CreateResult
{
    /**
     * @var Package
     */
    public $package;

    /**
     * CreateResult constructor.
     *
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }
}
