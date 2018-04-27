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

class UpdateData
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var TemplateObject
     */
    public $templateObject;

    /**
     * UpdateData constructor.
     *
     * @param Sheet          $sheet
     * @param TemplateData   $templateData
     * @param TemplateObject $templateObject
     */
    public function __construct(
        Sheet $sheet,
        TemplateData $templateData,
        TemplateObject $templateObject
    ) {
        $this->sheet          = $sheet;
        $this->templateData   = $templateData;
        $this->templateObject = $templateObject;
    }
}
