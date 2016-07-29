<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class EditableObject extends TemplateObject
{
    /**
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }
}
