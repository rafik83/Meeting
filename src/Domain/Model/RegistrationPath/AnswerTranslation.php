<?php

namespace Proximum\Vimeet\Domain\Model\RegistrationPath;

class AnswerTranslation
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $locale;

    /** @var Answer */
    private $answer;

    /** @var string */
    private $title;

    public function __construct(Answer $answer, string $locale, string $title)
    {
        $this->answer = $answer;
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

    public function getAnswer(): Answer
    {
        return $this->answer;
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
