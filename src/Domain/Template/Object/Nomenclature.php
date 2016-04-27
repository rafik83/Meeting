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

class Nomenclature extends Object
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContent() ?: '';
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return isset($this->data['value']) ? $this->data['value'] : null;
    }

    /**
     * @param string $content
     *
     * @return EditableText
     */
    public function setContent($content)
    {
        $this->data['value'] = $content;

        return $this;
    }
}
