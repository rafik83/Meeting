<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class Tag extends TemplateObject implements SearchableObjectInterface
{
    /**
     * @return array|string
     */
    public function getSearchableContent()
    {
        return $this->getTaggedDataContent();
    }
}
