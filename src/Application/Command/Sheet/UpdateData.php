<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class UpdateData implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var TemplateData */
    public $templateData;

    /** @var null|TemplateObject */
    public $templateObject;

    public function __construct(
        Sheet $sheet,
        TemplateData $templateData,
        ?TemplateObject $templateObject = null
    ) {
        $this->sheet = $sheet;
        $this->templateData = $templateData;
        $this->templateObject = $templateObject;
    }
}
