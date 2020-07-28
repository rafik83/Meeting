<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Upload;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Template\TemplateObject\Video;

class VideoUpload implements Command
{
    /** @var Video */
    public $videoObject;

    public function __construct(
        Video $videoObject
    ) {
        $this->videoObject = $videoObject;
    }
}
