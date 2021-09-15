<?php

namespace Proximum\Vimeet\Application\View\Unavailability\Mass;

use Proximum\Vimeet\Application\View\Agenda\AbstractTimeEntityView;

abstract class AbstractMassView extends AbstractTimeEntityView
{
    /** @var int */
    public $id;

    /** @var string */
    public $picto;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var string */
    public $leftColor;

    /** @var string */
    public $rightColor;

    /** @var string */
    public $timeZone;

    /** @var bool */
    public $isBlocking;

    /**
     * @param int                $id
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $title
     * @param string             $description
     * @param string             $picto
     * @param string             $leftColor
     * @param string             $rightColor
     * @param string             $timeZone
     * @param bool               $isBlocking
     */
    public function __construct(
        $id,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $title,
        $description,
        $picto,
        $leftColor,
        $rightColor,
        $timeZone,
        bool $isBlocking
    ) {
        $this->id          = $id;
        $this->begin       = $begin;
        $this->end         = $end;
        $this->title       = $title;
        $this->description = $description;
        $this->picto       = $picto;
        $this->leftColor   = $leftColor;
        $this->rightColor  = $rightColor;
        $this->timeZone    = $timeZone;
        $this->isBlocking  = $isBlocking;
    }
}
