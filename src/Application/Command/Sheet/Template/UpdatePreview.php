<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class UpdatePreview
{
    /**
     * @var array
     */
    public $previewObjects;

    /**
     * @var TemplateObject[]
     */
    public $templateObjects;

    /**
     * @var SheetTemplate
     */
    public $sheetTemplate;

    /**
     * UpdatePreview constructor.
     *
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
