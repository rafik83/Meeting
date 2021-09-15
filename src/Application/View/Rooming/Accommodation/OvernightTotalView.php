<?php

namespace Proximum\Vimeet\Application\View\Rooming\Accommodation;

class OvernightTotalView
{
    /** @var string */
    public $index;

    /** @var \DateTimeInterface */
    public $date;

    /** @var int */
    public $total;

    /** @var int */
    public $remaining;

    public function __construct(
        string $index,
        \DateTimeInterface $date,
        int $total = 0
    ) {
        $this->index = $index;
        $this->date = $date;
        $this->total = $total;
        $this->remaining = $total;
    }

    public function addToTotal(int $addToTotal): void
    {
        $this->total += $addToTotal;
        $this->remaining += $addToTotal;
    }

    public function decreaseRemaining(): void
    {
        $this->remaining--;
    }
}
