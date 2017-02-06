<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class EditableText extends EditableObject implements ContentObjectInterface, SearchableObjectInterface, ExportableObjectInterface
{
    /**
     * @param string|null locale
     * @return null|string
     */
    public function getContent($locale = null)
    {
        $thisLocale = $locale === null ? $this->locale : $locale;

        if ($this->isTranslatable()) {
            return isset($this->data['text'][$thisLocale]) ? $this->data['text'][$thisLocale] : null;
        }

        if (isset($this->data['text'])) {
            if (is_array($this->data['text'])) {
                return null;
            } else {
                return $this->data['text'];
            }
        }

        return null;
    }

    /**
     * @param string $content
     *
     * @return EditableText
     */
    public function setContent($content)
    {
        if ($this->isTranslatable()) {
            if (isset($this->data['text']) && is_array($this->data['text'])) {
                $this->data['text'][$this->locale] = $content;
            } else {
                $this->data['text'] = [];
                $this->data['text'][$this->locale] = $content;
            }
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
    public function getContentValueLocalize($locale)
    {
        $data = $this->getContent($locale);

        return $data !== null ? $data : '';
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
    public function isTitle()
    {
        return $this->getOption('type') === 'title';
    }

    /**
     * @return bool
     */
    public function isTextarea()
    {
        return $this->getOption('type') === 'textarea';
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
        return !empty($this->getOption('maxLength'))
            && null !== $this->getOption('maxLength')
            && '' !== $this->getOption('maxLength');
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

    /**
     * {@inheritdoc}
     */
    public function getSearchableContent()
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableFieldname($locale, $fallback)
    {
        return $this->getLabel($locale, $fallback);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableContent(array $taggedData = [])
    {
        $result = $this->getContentValue();

        if (!empty($result)) {
            return $result;
        }

        if (isset($taggedData[$this->getTag()])) {
            return $taggedData[$this->getTag()];
        }

        return '';
    }
}
