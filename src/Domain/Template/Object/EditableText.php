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

class EditableText extends Object
{
    /**
     * @return string
     */
    public function getContent()
    {
        return isset($this->data['text'][$this->locale]) ? $this->data['text'][$this->locale] : null;
    }

    /**
     * @param string $content
     *
     * @return EditableText
     */
    public function setContent($content)
    {
        $this->data['text'][$this->locale] = $content;

        return $this;
    }
}
