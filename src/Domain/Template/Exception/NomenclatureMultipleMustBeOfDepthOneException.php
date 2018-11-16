<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Exception;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class NomenclatureMultipleMustBeOfDepthOneException extends TemplateException
{
    /** @var TemplateObject[] */
    public $templateObjects = [];

    public function __construct(array $templateObjects)
    {
        $this->templateObjects = $templateObjects;

        parent::__construct('validator.template.nomenclatureCheckboxesMustBeOfDepthOne', 422);
    }
}
