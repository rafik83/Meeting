<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;

class TemplateBooleanFilterIdentifier
{
    public function getBooleanFilterValues(TemplateData $templateData)
    {
        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof BooleanObject) {

            }
        }
    }

    public function getBooleanFilters()
    {

    }
}
