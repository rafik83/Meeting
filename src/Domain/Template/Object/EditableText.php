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
    public function getContentLabel()
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setContent($value);
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return (bool) $this->getOption('required');
    }

    /**
     * @return bool
     */
    public function hasMinLength()
    {
        return !empty($this->getOption('minLength'));
    }

    /**
     * @return int
     */
    public function getMinLength()
    {
        return (int) $this->getOption('minLength');
    }

    /**
     * @return bool
     */
    public function hasMaxLength()
    {
        return !empty($this->getOption('maxLength'));
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return (int) $this->getOption('maxLength');
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->getOption('tag');
    }

    /**
     * Get fallback content if object is translatable.
     *
     * @return string|null
     */
    public function getFallbackContent()
    {
        if ($this->isTranslatable() && isset($this->data['text']) && is_array($this->data['text'])
            || isset($this->data['text']) && is_array($this->data['text'])
        ) {
            return isset($this->data['text'][$this->getFallback()])
                ? $this->data['text'][$this->getFallback()]
                : null;
        }

        return null;
    }
}
