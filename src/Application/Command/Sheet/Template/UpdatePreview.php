<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class UpdatePreview implements Command
{
    /** @var array */
    public $previewObjects;

    /** @var TemplateObject[] */
    public $templateObjects;

    /** @var SheetTemplate */
    public $sheetTemplate;

    /**
     * @param SheetTemplate    $sheetTemplate
     * @param TemplateObject[] $templateObjects
     */
    public function __construct(SheetTemplate $sheetTemplate, array $templateObjects)
    {
        $this->sheetTemplate   = $sheetTemplate;
        $this->templateObjects = $templateObjects;
        $this->previewObjects  = $sheetTemplate->getPreview();
    }
}
