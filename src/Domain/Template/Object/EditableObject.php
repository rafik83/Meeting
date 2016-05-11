<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;

class EditableObject extends Object
{
    /**
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }
}
