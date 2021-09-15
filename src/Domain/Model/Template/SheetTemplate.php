<?php

namespace Proximum\Vimeet\Domain\Model\Template;

use Proximum\Vimeet\Domain\Model\Event;

class SheetTemplate extends AbstractTemplate
{
    /** @var array */
    protected $preview;

    /** @var array */
    protected $printValue;

    public function __construct(
        string $title,
        array $value,
        array $locales,
        string $fallback,
        \DateTimeInterface $createdAt,
        array $preview = [],
        ?Event $event = null
    ) {
        parent::__construct($title, $value, $locales, $fallback, $createdAt, $event);

        $this->preview = $preview;
    }

    public function getFallback(): string
    {
        return $this->event ? $this->event->getLocaleFallback() : $this->fallback;
    }

    public function update(string $title, string $fallback): self
    {
        if (!$this->hasLocale($fallback)) {
            throw new \InvalidArgumentException('Default locale should be in the template locales.');
        }

        $this->title    = $title;
        $this->fallback = $fallback;

        return $this;
    }

    public function getPreview(): array
    {
        return $this->preview;
    }

    public function setPreview(array $preview): self
    {
        $this->preview = $preview;

        return $this;
    }

    public function getAvailableLocale(string $locale): string
    {
        if (in_array($locale, $this->getLocales())) {
            return $locale;
        }

        return $this->getFallback();
    }

    public function getPrintValue(): array
    {
        return $this->printValue;
    }

    public function setPrintValue(array $printValue)
    {
        $this->printValue = $printValue;
    }
}
