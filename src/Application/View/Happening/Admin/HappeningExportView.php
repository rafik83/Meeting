<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class HappeningExportView
{
    /** @var string */
    private $title;

    /** @var string|null */
    private $description;

    /** @var string */
    private $category;

    /** @var string */
    private $begin;

    /** @var string */
    private $end;

    public function __construct(string $title, ?string $description, string $category, string $begin, string $end)
    {
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getBegin(): string
    {
        return $this->begin;
    }

    /**
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }
}
