<?php

namespace Proximum\Vimeet\Application\View\Unavailability\Mass;

class MassAssignementView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var \DateTimeInterface
     */
    public $massBegin;

    /**
     * @var \DateTimeInterface
     */
    public $massEnd;

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
    public $enabled;

    /**
     * @var string
     */
    public $eventTimezone;

    /**
     * @var string
     */
    public $serverTimezone;

    /**
     * MassAssignementView constructor.
     *
     * @param int                $id
     * @param string             $title
     * @param \DateTimeInterface $massBegin
     * @param \DateTimeInterface $massEnd
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $enabled
     * @param string             $eventTimezone
     * @param string             $serverTimezone
     */
    public function __construct(
        $id,
        $title,
        \DateTimeInterface $massBegin,
        \DateTimeInterface $massEnd,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $enabled,
        $eventTimezone,
        $serverTimezone
    ) {
        $this->id             = $id;
        $this->begin          = $begin;
        $this->end            = $end;
        $this->enabled        = $enabled;
        $this->massBegin      = $massBegin;
        $this->massEnd        = $massEnd;
        $this->title          = $title;
        $this->eventTimezone  = $eventTimezone;
        $this->serverTimezone = $serverTimezone;
    }
}
