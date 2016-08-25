<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class UpdatePreview
{
    /**
     * @var TemplateObject[]
     */
    public $previewObjects;

    /**
     * @var TemplateObject[]
     */
    public $templateObjects;

    /**
     * UpdatePreview constructor.
     *
     * @param TemplateObject[] $templateObjects
     */
    public function __construct(array $templateObjects)
    {
        $this->templateObjects = $templateObjects;
    }

}
