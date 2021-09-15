<?php

namespace Proximum\Vimeet\Application\View\Schedule;

class SlotView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var bool
     */
    public $locked;

    /**
     * @var string
     */
    public $duration;

    /**
     * @var bool
     */
    public $disabled;

    /**
     * SlotView constructor.
     *
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param int                $id
     * @param string             $duration
     * @param bool               $locked
     * @param bool               $disabled
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $id,
        $duration,
        $locked,
        $disabled
    ) {
        $this->begin    = $begin;
        $this->end      = $end;
        $this->id       = $id;
        $this->duration = $duration;
        $this->locked   = $locked;
        $this->disabled = $disabled;
    }
}
