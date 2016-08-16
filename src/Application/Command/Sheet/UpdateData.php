<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;

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
     * UpdateData constructor.
     *
     * @param Sheet        $sheet
     * @param TemplateData $templateData
     *
     */
    public function __construct(Sheet $sheet, TemplateData $templateData)
    {
        $this->sheet        = $sheet;
        $this->templateData = $templateData;
    }
}
