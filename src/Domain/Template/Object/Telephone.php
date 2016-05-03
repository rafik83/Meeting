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

class Telephone extends Object
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getData() ? $this->getData() : '';
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getOption('content');
    }
}
