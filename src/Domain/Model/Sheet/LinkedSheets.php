<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class LinkedSheets
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var ArrayCollection of Sheet */
    private $sheets;

    public function __construct(Event $event, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->createdAt = $createdAt;
        $this->sheets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets(): array
    {
        return $this->sheets->toArray();
    }

    public function countSheets(): int
    {
        return $this->sheets->count();
    }
}
