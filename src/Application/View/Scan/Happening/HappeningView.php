<?php

namespace Proximum\Vimeet\Application\View\Scan\Happening;

class HappeningView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var \DateTimeInterface */
    public $begin;

    public function __construct(int $id, string $title, \DateTimeInterface $begin)
    {
        $this->id = $id;
        $this->title = $title;
        $this->begin = $begin;
    }
}
