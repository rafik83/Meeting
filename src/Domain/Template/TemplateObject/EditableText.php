<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TranslatableInterface;

class EditableText extends EditableObject implements ContentObjectInterface, SearchableObjectInterface, ExportableObjectInterface, TranslatableInterface
{
    /**
     * @param string|null locale
     *
     * @return null|string
     */
    public function getContent($locale = null)
    {
        $thisLocale = null === $locale ? $this->locale : $locale;

        if ($this->isTranslatable()) {
            if (null === $thisLocale) {
                return null;
            }

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
        return $this->getContentValueLocalize();
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        if (!isset($this->data['text'])) {
            return true;
        }

        if ($this->isTranslatable()) {
            foreach ($this->data['text'] as $translatedText) {
                if ('' !== trim($translatedText)) {
                    return false;
                }
            }

            return true;
        }

        return '' === trim($this->data['text']);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValueLocalize($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        $data = $this->getContent($locale);

        return null !== $data
            ? ($this->isTranslatable() && \is_array($data) && \array_key_exists('content', $data) ? $data['content'] : $data)
            : '';
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
        return 'title' === $this->getOption('type');
    }

    /**
     * @return bool
     */
    public function isTextarea()
    {
        return 'textarea' === $this->getOption('type');
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
        return $this->getContentValue() ?: $this->getTaggedDataContent();
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
    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        $result = $this->getContentValueLocalize($locale);

        if (!empty($result)) {
            return $result;
        }

        if (isset($taggedData[$this->getTag()])) {
            if (\is_array($taggedData[$this->getTag()])) {
                return implode(', ', $taggedData[$this->getTag()]);
            }

            return $taggedData[$this->getTag()];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations(array $locales = [])
    {
        if (!is_array($this->data) || !isset($this->data['text'])) {
            return [];
        }

        if (!$this->isTranslatable() || !is_array($this->data['text'])) {
            return [];
        }

        $translations = [];

        foreach ($this->data['text'] as $locale => $content) {
            $translations[$locale] = $content;
        }

        return $translations;
    }

    /**
     * @see EditableTextTranslationType
     *
     * @return array
     */
    public function getTranslationsInput()
    {
        if (!\is_array($this->data) || !isset($this->data['text'])) {
            return [];
        }

        if (!$this->isTranslatable() || !\is_array($this->data['text'])) {
            return [];
        }

        $translations = [];

        foreach ($this->data['text'] as $locale => $content) {
            if (\is_array($content) && array_key_exists('content', $content)) {
                $translations[$locale]['content'] = $content['content'];
            } else {
                $translations[$locale]['content'] = $content;
            }
        }

        return $translations;
    }

    /**
     * @param array $translations "['fr' => ['content' => 'Contenu fr'], 'en' => ['content' => 'En content']]"
     *
     * @see EditableTextTranslationType
     *
     * @throws \LogicException
     */
    public function setTranslationsInput(array $translations = [])
    {
        $this->setTranslations($translations);
    }

    /**
     * @param array $translations "['fr' => 'contenu', 'en' => 'content']"
     *
     * @throws \LogicException
     */
    public function setTranslations(array $translations = [])
    {
        if (!$this->isTranslatable()) {
            throw new \LogicException(sprintf('Object %s is not translatable', $this->getKey()));
        }

        $this->data['text'] = []; // erase previous untranslatable value

        foreach ($translations as $locale => $translation) {
            if (\is_array($translation) && array_key_exists('content', $translation)) {
                $this->data['text'][$locale] = $translation['content'];
            } else {
                $this->data['text'][$locale] = $translation;
            }
        }
    }
}
