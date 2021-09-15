<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping as SheetImportMapping;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Import implements Command
{
    /** @var Type */
    public $type;

    /** @var UploadedFile */
    public $file;

    /** @var string */
    public $charset;

    /** @var bool */
    public $allowMultiSheet;

    /** @var SheetImportMapping|null */
    public $mapping;
}
