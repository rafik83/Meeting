<?php

namespace Proximum\Vimeet\Application\Query\User\Event;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class AuthenticationTokenImportPreviewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var File */
    public $importedFile;

    public function __construct(Event $event, File $importedFile)
    {
        $this->event = $event;
        $this->importedFile = $importedFile;
    }
}
