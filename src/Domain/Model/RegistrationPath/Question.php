<?php

namespace Proximum\Vimeet\Domain\Model\RegistrationPath;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Type\RegistrationPath\View\AnswerView;

class Question
{
    /** @var int|null */
    private $id;

    /** @var Event */
    private $event;

    /** @var ArrayCollection of QuestionTranslation */
    private $translations;

    /** @var ArrayCollection of AnswerView */
    private $answers;

    /** @var Answer|null */
    private $previousAnswer;

    public function __construct(Event $event, ?Answer $previousAnswer)
    {
        $this->event = $event;
        $this->translations = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->previousAnswer = $previousAnswer;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
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

    public function getTitle(string $locale): string
    {
        $translation = $this->getTranslation($locale);

        if (null === $translation) {
            return '';
        }

        return $translation->getTitle();
    }

    /**
     * @return Answer[]
     */
    public function getAnswers(): array
    {
        return $this->answers->toArray();
    }

    /**
     * @param AnswerView[] $answerViews
     */
    public function setAnswers(array $answerViews): void
    {
        foreach ($this->answers as $key => $answer) {
            if (!isset($answerViews[$key])) {
                $this->answers->remove($key);
            }
        }

        foreach ($answerViews as $key => $answerView) {
            $foundAnswer = $this->answers->get($key);

            if ($foundAnswer instanceof Answer) {
                foreach ($answerView->translatedTitle as $locale => $title) {
                    $foundAnswer->translate($locale, $title);
                }

                continue;
            }

            $newAnswer = new Answer($this);

            foreach ($answerView->translatedTitle as $locale => $title) {
                $newAnswer->translate($locale, $title);
            }

            $this->answers->set($key, $newAnswer);
        }
    }

    private function getTranslation(string $locale): ?QuestionTranslation
    {
        return $this->translations->get($locale);
    }

    public function getPreviousAnswer(): ?Answer
    {
        return $this->previousAnswer;
    }
}
