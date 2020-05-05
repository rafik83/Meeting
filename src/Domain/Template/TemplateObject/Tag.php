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
     * @param string|null $locale
     *
     * @return array|string
     */
    public function getSearchableContent(?string $locale = null)
    {
        return $this->getTaggedDataContent($locale);
    }
}
