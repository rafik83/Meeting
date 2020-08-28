<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Upload;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Template\TemplateObject\Video;

class VideoUpload implements Command
{
    /** @var Video */
    public $videoObject;

    /** @var Event */
    public $event;

    public function __construct(
        Event $event,
        Video $videoObject
    ) {
        $this->videoObject = $videoObject;
        $this->event = $event;
    }
}
