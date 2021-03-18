<?php

namespace Proximum\Vimeet\Domain\View;

class MeetingView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $sheetNameFrom;

    /**
     * @var string
     */
    public $sheetNameTo;

    /**
     * @var \DateTimeInterface
     */
    public $slotBegin;

    /**
     * @var \DateTimeInterface
     */
    public $slotEnd;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var int
     */
    public $sheetFromId;

    /**
     * @var int
     */
    public $sheetToId;

    /** @var string */
    public $createdType;

    public function __construct(
        int $id,
        int $sheetFromId,
        int $sheetToId,
        string $sheetNameFrom,
        string $sheetNameTo,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $slotBegin,
        \DateTimeInterface $slotEnd,
        string $createdType
    ) {
        $this->id                      = $id;
        $this->sheetFromId             = $sheetFromId;
        $this->sheetToId               = $sheetToId;
        $this->sheetNameFrom           = $sheetNameFrom;
        $this->sheetNameTo             = $sheetNameTo;
        $this->createdAt               = $createdAt;
        $this->slotBegin               = $slotBegin;
        $this->slotEnd                 = $slotEnd;
        $this->createdType = $createdType;
    }
}
