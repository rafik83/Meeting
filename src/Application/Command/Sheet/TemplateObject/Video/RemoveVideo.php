<?php

namespace Proximum\Vimeet\Application\Command\Sheet\TemplateObject\Video;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Video;

class RemoveVideo implements Command
{
    /** @var Video */
    public $video;

    /** @var Sheet */
    public $sheet;

    /** @var TemplateData */
    public $templateData;

    public function __construct(Video $video, Sheet $sheet, TemplateData $templateData)
    {
        $this->video = $video;
        $this->sheet = $sheet;
        $this->templateData = $templateData;
    }
}
