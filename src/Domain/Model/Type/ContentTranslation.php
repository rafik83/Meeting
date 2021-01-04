<?php

namespace Proximum\Vimeet\Domain\Model\Type;

class ContentTranslation
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $locale;

    /** @var Content */
    private $content;

    /** @var string */
    private $value;

    public function __construct(Content $content, string $locale, string $value)
    {
        $this->content = $content;
        $this->locale  = $locale;
        $this->value   = $value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function update(string $value): void
    {
        $this->value = $value;
    }
}
