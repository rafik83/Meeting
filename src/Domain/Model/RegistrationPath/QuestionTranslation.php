<?php

namespace Proximum\Vimeet\Domain\Model\RegistrationPath;

class QuestionTranslation
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $locale;

    /** @var Question */
    private $question;

    /** @var string */
    private $title;

    public function __construct(Question $question, string $locale, string $title)
    {
        $this->question = $question;
        $this->locale = $locale;
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function update(string $title): void
    {
        $this->title = $title;
    }
}
