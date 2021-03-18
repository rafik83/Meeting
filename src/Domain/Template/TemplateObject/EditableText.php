<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TranslatableInterface;

class EditableText extends EditableObject implements ContentObjectInterface, SearchableObjectInterface, ExportableObjectInterface, TranslatableInterface
{
    public const CONTENT = 'content';
    public const TEXT = 'text';

    /**
     * @param string|null locale
     *
     * @return null|string
     */
    public function getContent($locale = null)
    {
        $locale = $locale ?? $this->locale;

        if ($this->isTranslatable()) {
            if (null === $locale) {
                return null;
            }

            if (isset($this->data[self::TEXT][$locale])) {
                $content = $this->data[self::TEXT][$locale];

                return \is_array($content) ? implode(', ', $content) : $content;
            }

            return null;
        }

        if (isset($this->data[self::TEXT])) {
            $content = $this->data[self::TEXT];

            return \is_array($content) ? implode(', ', $content) : $content;
        }

        return null;
    }

    public function eraseData(): void
    {
        if ($this->isTranslatable()) {
            $this->data[self::TEXT] = [];
        } else {
            $this->data[self::TEXT] = null;
        }
    }

    /**
     * @param string|array $content
     *
     * @return EditableText
     */
    public function setContent($content)
    {
        if ($this->isTranslatable()) {
            if (isset($this->data[self::TEXT]) && is_array($this->data[self::TEXT])) {
                $this->data[self::TEXT][$this->locale] = $content;
            } else {
                $this->data[self::TEXT] = [];
                $this->data[self::TEXT][$this->locale] = $content;
            }
        } else {
            $this->data[self::TEXT] = $content;
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
        if (!isset($this->data[self::TEXT])) {
            return true;
        }

        if ($this->isTranslatable() || \is_array($this->data[self::TEXT])) {
            foreach ($this->data[self::TEXT] as $translatedText) {
                if ('' !== trim($translatedText)) {
                    return false;
                }
            }

            return true;
        }

        return '' === trim($this->data[self::TEXT]);
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
            ? ($this->isTranslatable() && \is_array($data) && \array_key_exists(self::CONTENT, $data) ? $data[self::CONTENT] : $data)
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
        if ($this->isTranslatable() && isset($this->data[self::TEXT]) && is_array($this->data[self::TEXT])
            || isset($this->data[self::TEXT]) && is_array($this->data[self::TEXT])
        ) {
            return isset($this->data[self::TEXT][$this->getFallback()])
                ? $this->data[self::TEXT][$this->getFallback()]
                : null;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableContent(?string $locale = null)
    {
        return $this->getContentValueLocalize($locale) ?: $this->getTaggedDataContent($locale);
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
        if (!is_array($this->data) || !isset($this->data[self::TEXT])) {
            return [];
        }

        if (!$this->isTranslatable() || !is_array($this->data[self::TEXT])) {
            return [];
        }

        $translations = [];

        foreach ($this->data[self::TEXT] as $locale => $content) {
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
        if (!\is_array($this->data) || !isset($this->data[self::TEXT])) {
            return [];
        }

        if (!$this->isTranslatable() || !\is_array($this->data[self::TEXT])) {
            return [];
        }

        $translations = [];

        foreach ($this->data[self::TEXT] as $locale => $content) {
            if (\is_array($content) && array_key_exists(self::CONTENT, $content)) {
                $translations[$locale][self::CONTENT] = $content[self::CONTENT];
            } else {
                $translations[$locale][self::CONTENT] = $content;
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
     * or for objects collection: "['fr' => ['contenu 1', 'contenu 2'], 'en' => ['content 1', 'content 2']]"
     *
     * @throws \LogicException
     */
    public function setTranslations(array $translations = [])
    {
        if (!$this->isTranslatable()) {
            throw new \LogicException(sprintf('Object %s is not translatable', $this->getKey()));
        }

        $this->data[self::TEXT] = []; // erase previous untranslatable value

        foreach ($translations as $locale => $translation) {
            if (\is_array($translation) && array_key_exists(self::CONTENT, $translation)) {
                $this->data[self::TEXT][$locale] = $translation[self::CONTENT];
            } else {
                $this->data[self::TEXT][$locale] = $translation;
            }
        }
    }
}
