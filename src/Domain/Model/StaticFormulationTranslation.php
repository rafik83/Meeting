<?php

namespace Proximum\Vimeet\Domain\Model;

class StaticFormulationTranslation
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $locale;

    /** @var StaticFormulation */
    private $staticFormulation;

    /** @var string */
    private $title;

    /**
     * @param StaticFormulation $staticFormulation
     * @param string            $locale
     * @param string            $title
     */
    public function __construct(StaticFormulation $staticFormulation, string $locale, string $title)
    {
        $this->staticFormulation = $staticFormulation;
        $this->locale = $locale;
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return StaticFormulation
     */
    public function getStaticFormulation(): StaticFormulation
    {
        return $this->staticFormulation;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    public function update(string $title): void
    {
        $this->title = $title;
    }
}
