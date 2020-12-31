<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Package;

class Duplicate
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
