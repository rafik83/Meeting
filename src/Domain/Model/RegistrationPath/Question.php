<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\RegistrationPath;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class Question
{
    /** @var int|null */
    private $id;

    /** @var Event */
    private $event;

    /** @var ArrayCollection of QuestionTranslation */
    private $translations;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    private function getTranslation(string $locale): ?QuestionTranslation
    {
        return $this->translations->get($locale);
    }

    public function translate(string $locale, string $title): void
    {
        $translation = $this->getTranslation($locale);

        if (null !== $translation) {
            $translation->update($title);
        } else {
            $this->translations->set($locale, new QuestionTranslation($this, $locale, $title));
        }
    }
}
