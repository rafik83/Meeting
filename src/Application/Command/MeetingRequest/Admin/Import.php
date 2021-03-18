<?php

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Import implements Command
{
    public Event $event;
    public ?UploadedFile $file;
    public ?string $charset;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
