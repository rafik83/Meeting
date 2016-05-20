<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\ProductsSelection;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\ProductsSelectionTemplate;
use Proximum\Vimeet\Domain\Template\TemplateData;

class Update
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var ProductsSelectionTemplate
     */
    public $template;

    /**
     * @param ProductsSelectionTemplate $template
     * @param TemplateData              $templateData
     */
    public function __construct(ProductsSelectionTemplate $template, TemplateData $templateData)
    {
        $this->template     = $template;
        $this->title        = $template->getTitle();
        $this->templateData = $templateData;
    }
}
