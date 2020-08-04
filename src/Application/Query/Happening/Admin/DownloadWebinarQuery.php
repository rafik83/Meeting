<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;

class DownloadWebinarQuery implements Query
{
    /** @var Happening */
    public $happening;

    /** @var bool regenerate archive, only for multiple files and for testing purposes */
    public $regenerate;

    public function __construct(Happening $happening, bool $regenerate = false)
    {
        $this->happening = $happening;
        $this->regenerate = $regenerate;
    }
}
