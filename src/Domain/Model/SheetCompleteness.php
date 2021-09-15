<?php

namespace Proximum\Vimeet\Domain\Model;

class SheetCompleteness
{
    /** @var int */
    private $id;

    /** @var Sheet */
    private $sheet;

    /** @var string */
    private $locale;

    /** @var int */
    private $completeness;

    public function __construct(
        Sheet $sheet,
        string $locale,
        int $completeness
    ) {
        $this->sheet = $sheet;
        $this->locale = $locale;
        $this->completeness = $completeness;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getCompleteness(): int
    {
        return $this->completeness;
    }

    public function setCompleteness(int $completeness): void
    {
        $this->completeness = $completeness;
    }
}
