<?php

namespace Proximum\Vimeet\Application\View\Planning\Day;

abstract class AbstractMassView extends AbstractTimeEntityView
{
    /** @var string */
    public $title;

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $title
     */
    public function __construct(\DateTimeInterface $begin, \DateTimeInterface $end, $title)
    {
        parent::__construct($begin, $end);

        $this->title = $title;
    }
}
