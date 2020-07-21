<?php

namespace Proximum\Vimeet\Application\Command\Participant\Import;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;

class UpdateMapping implements Command
{
    /** @var array */
    public $mapping;

    /** @var ImportMapping */
    public $importMapping;

    /** @var bool */
    public $save;

    public function __construct(
        ImportMapping $importMapping,
        array $mapping
    ) {
        $this->mapping = $mapping;
        $this->importMapping = $importMapping;
    }
}
