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
    public function __toString()
    {
        return $this->getContent() ?: '';
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        if ($this->isTranslatable()) {
            return isset($this->data['text'][$this->locale]) ? $this->data['text'][$this->locale] : null;
        }

        return isset($this->data['text']) ? $this->data['text'] : null;
    }

    /**
     * @param string $content
     *
     * @return EditableText
     */
    public function setContent($content)
    {
        if ($this->isTranslatable()) {
            $this->data['text'][$this->locale] = $content;
        } else {
            $this->data['text'] = $content;
        }

        return $this;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function missingRequiredData(array $data)
    {
        if (true === $this->getOption('required')) {
            return !empty($data[$this->getKey()]['text']);
        }

        return true;
    }
}
