<?php

namespace Proximum\Vimeet\Application\View\Meeting\Admin\Details;

class SlotView
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
