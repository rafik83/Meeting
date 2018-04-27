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
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;

class RemoveImage
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
