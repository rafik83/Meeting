<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;

class RemoveImage implements Command
{
    /**
     * @var Image
     */
    public $image;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * RemoveImage constructor.
     *
     * @param Image        $image
     * @param Sheet        $sheet
     * @param TemplateData $templateData
     */
    public function __construct(Image $image, Sheet $sheet, TemplateData $templateData)
    {
        $this->image        = $image;
        $this->sheet        = $sheet;
        $this->templateData = $templateData;
    }
}
