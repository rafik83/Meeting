<?php

namespace Proximum\Vimeet\Application\View\MeetingRequest\Export;

class MeetingRequestView
{
    /** @var int */
    public $id;

    /** @var int */
    public $meetingId;

    /** @var string */
    public $state;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var \DateTimeInterface */
    public $updatedAt;

    /** @var SheetView */
    public $fromSheet;

    /** @var SheetView */
    public $toSheet;

    /** @var null|string */
    public $createdType;

    /** @var null|\DateTime */
    public $slotBeginDate;

    public function __construct(
        int $id,
        int $meetingId = null,
        SheetView $fromSheet,
        SheetView $toSheet,
        string $state,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $updatedAt,
        ?string $createdType,
        ?\DateTime $slotBeginDate
    ) {
        $this->id = $id;
        $this->meetingId = $meetingId;
        $this->fromSheet = $fromSheet;
        $this->toSheet = $toSheet;
        $this->state = $state;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->createdType = $createdType;
        $this->slotBeginDate = $slotBeginDate;
    }
}
