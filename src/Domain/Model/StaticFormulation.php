<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
    private $key;

    public function __construct(
        Event $event,
        string $key,
        array $types = []
    ) {
        $this->event = $event;
        $this->key = $key;
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
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
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
        return $this->key;
    }
}
