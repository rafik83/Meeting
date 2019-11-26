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

class Answer
{
    /** @var int|null */
    private $id;

    /** @var Question */
    private $question;

    /** @var ArrayCollection of AnswerTranslation */
    private $translations;

    public function __construct(Question $question)
    {
        $this->question = $question;
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->question->getEvent();
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getTitle(string $locale): string
    {
        $translation = $this->getTranslation($locale);

        if (null === $translation) {
            return '';
        }

        return $translation->getTitle();
    }

    public function translate(string $locale, string $title): void
    {
        $translation = $this->getTranslation($locale);

        if (null !== $translation) {
            $translation->update($title);
        } else {
            $this->translations->set($locale, new AnswerTranslation($this, $locale, $title));
        }
    }

    private function getTranslation(string $locale): ?AnswerTranslation
    {
        return $this->translations->get($locale);
    }
}
