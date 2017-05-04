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
     * @var boolean
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
     * @param int                $id
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $duration
     * @param bool               $locked
     * @param bool               $disabled
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $duration,
        $id,
        $locked,
        $disabled
    ) {
        $this->begin    = $begin;
        $this->end      = $end;
        $this->duration = $duration;
        $this->id       = $id;
        $this->locked   = $locked;
        $this->disabled = $disabled;
    }
}
