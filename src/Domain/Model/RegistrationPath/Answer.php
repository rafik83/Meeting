<?php

namespace Proximum\Vimeet\Domain\Model\RegistrationPath;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Answer
{
    /** @var int|null */
    private $id;

    /** @var Question */
    private $question;

    /** @var ArrayCollection of AnswerTranslation */
    private $translations;

    /** @var ArrayCollection of Type */
    private $types;

    /** @var ArrayCollection of Question */
    private $nextQuestions;

    public function __construct(Question $question)
    {
        $this->question = $question;
        $this->translations = new ArrayCollection();
        $this->types = new ArrayCollection();
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

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    public function removeTypes(): void
    {
        $this->types = new ArrayCollection();
    }

    public function hasAlreadyNextStep(): bool
    {
        return !$this->types->isEmpty() || $this->hasNexQuestion();
    }

    public function hasNexQuestion(): bool
    {
        return null !== $this->getNextQuestion();
    }

    public function getNextQuestion(): ?Question
    {
        $nextQuestion = $this->nextQuestions->first();

        if (false === $nextQuestion) {
            return null;
        }

        return $nextQuestion;
    }
}
