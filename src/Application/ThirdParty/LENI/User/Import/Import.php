<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\User\Import;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Import implements Command
{
    /** @var Event */
    public $event;

    /** @var UploadedFile */
    public $file;

    /** @var string */
    public $charset;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
