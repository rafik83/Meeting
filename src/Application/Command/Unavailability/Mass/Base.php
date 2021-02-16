<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;

abstract class Base
{
    /**
     * @var Category
     */
    public $category;

    /**
     * @var string
     */
    public $name;

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
    public $blocking;

    /**
     * @var array
     */
    public $translations;

    /**
     * Is dispatch enable
     *
     * @var bool
     */
    public $dispatch;

    /**
     * Dispatch time slots
     *
     * @var array
     */
    public $timeSlots = [];

    /** @var Type[] */
    public $types = [];

    /**
     * Check if timeSlots are include in begin and end dates.
     *
     * @return bool
     */
    public function areTimeSlotsValid()
    {
        foreach ($this->timeSlots as $timeSlot) {
            if ($timeSlot['from'] < $this->begin || $this->end < $timeSlot['to']) {
                return false;
            }
        }

        return true;
    }
}
