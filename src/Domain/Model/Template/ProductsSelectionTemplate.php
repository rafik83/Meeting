<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Template;

use Proximum\Vimeet\Domain\Model\Event;

class ProductsSelectionTemplate extends AbstractTemplate
{
    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->event->getFallback();
    }
}
