<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class StaticFormulation
{
    /** @var int|null */
    private $id;

    /** @var Event */
    private $event;

    /** @var ArrayCollection */
    private $types;

    /** @var StaticFormulationTranslation */
    private $translations;

    /** @var string */
    private $staticFormulationKey;

    public function __construct(
        Event $event,
        string $staticFormulationKey,
        array $types = []
    ) {
        $this->event = $event;
        $this->staticFormulationKey = $staticFormulationKey;
        $this->types = new ArrayCollection($types);
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    public function update(array $types): void
    {
        $this->types = new ArrayCollection($types);
    }

    /**
     * @return StaticFormulationTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function translate(string $locale, string $title): void
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($title);
        } else {
            $this->translations->set($locale, new StaticFormulationTranslation($this, $locale, $title));
        }
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->staticFormulationKey;
    }

    public function getTitle(string $locale): string
    {
        if ($this->hasTranslation($locale)) {
            return $this->translations->get($locale)->getTitle();
        }

        return '';
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }
}
