<?php

namespace Proximum\Vimeet\Domain\View\Sheet;

class SheetIdView
{
    /** @var int */
    private $id;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
