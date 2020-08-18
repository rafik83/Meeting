<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class ZipRecordArchive implements Command
{
    /** @var Happening */
    public $happening;

    /** @var bool regenerate archive, only for multiple files and for testing purposes */
    public $regenerate;

    public function __construct(
        Happening $happening,
        bool $regenerate = false
    ) {
        $this->happening = $happening;
        $this->regenerate = $regenerate;
    }
}
