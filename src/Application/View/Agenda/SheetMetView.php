<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class SheetMetView
{
    /** @var string */
    private $title;

    /** @var bool */
    private $isHighlighted;

    public function __construct(string $title, bool $isHighlighted)
    {
        $this->title = $title;
        $this->isHighlighted = $isHighlighted;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isHighlighted(): bool
    {
        return $this->isHighlighted;
    }
}
