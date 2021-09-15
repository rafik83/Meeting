<?php

namespace Proximum\Vimeet\Application\View\Planning\Day;

abstract class AbstractTimeEntityView
{
    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function __construct(\DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->begin = $begin;
        $this->end   = $end;
    }
}
