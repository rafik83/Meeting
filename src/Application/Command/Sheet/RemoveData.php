<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class RemoveData
{
    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var TemplateObject
     */
    public $templateObject;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * RemoveData constructor.
     *
     * @param TemplateData   $templateData
     * @param TemplateObject $templateObject
     * @param Sheet          $sheet
     */
    public function __construct(TemplateData $templateData, TemplateObject $templateObject, Sheet $sheet)
    {
        $this->templateData   = $templateData;
        $this->templateObject = $templateObject;
        $this->sheet          = $sheet;
    }
}
