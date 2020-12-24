<?php

namespace Proximum\Vimeet\Application\View\Planning\Day;

class UnavailabilityView extends AbstractTimeEntityView
{
    /**
     * @var null|string
     */
    public $message;

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param null|string        $message
     */
    public function __construct(\DateTimeInterface $begin, \DateTimeInterface $end, $message = null)
    {
        parent::__construct($begin, $end);

        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return null !== $this->message;
    }
}
