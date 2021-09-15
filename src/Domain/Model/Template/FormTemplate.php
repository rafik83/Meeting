<?php

namespace Proximum\Vimeet\Domain\Model\Template;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class FormTemplate extends AbstractTemplate
{
    /** @var ArrayCollection of FormTemplateTranslation */
    private $translations;

    /** @var bool */
    private $published;

    public function __construct(
        Event $event,
        string $title,
        array $value,
        array $locales,
        string $fallback,
        \DateTimeInterface $createdAt
    ) {
        parent::__construct($title, $value, $locales, $fallback, $createdAt, $event);

        $this->translations = new ArrayCollection();
        $this->published = false;
    }

    /**
     * @return FormTemplateTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function translateTitles(array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            if ($this->hasTranslation($locale)) {
                $this->translations->get($locale)->update($translation['title']);
            } else {
                $this->translations->set($locale, new FormTemplateTranslation($this, $locale, $translation['title']));
            }
        }
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    public function getLocalizedTitle(string $locale): string
    {
        if (!$this->hasTranslation($locale)) {
            return '';
        }

        return $this->translations->get($locale)->getTitle();
    }

    public function getFallback(): string
    {
        return $this->event ? $this->event->getFallback() : $this->fallback;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function getTranslatedTitle(string $locale): string
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getTitle() : '';
    }

    public function update(string $title, array $translations, bool $isPublished): void
    {
        $this->setTitle($title);
        $this->translateTitles($translations);
        $this->published = $isPublished;
    }
}
