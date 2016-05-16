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

class EditableText extends EditableObject implements ContentObjectInterface
{
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
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getContent() ? $this->getContent() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setContent($value);
    }
}
