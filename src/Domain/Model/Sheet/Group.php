<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

/**
 * "Entité multi-fiches"
 * sheet_group table
 */
class Group
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var User */
    private $manager;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var string */
    private $title;

    /** @var ArrayCollection of Sheet */
    private $sheets;

    /** @var Group|null */
    private $duplicatedFrom;

    /** @var bool */
    private $sheetTitleForced;

    /**
     * @param Event              $event
     * @param User               $manager
     * @param string             $title
     * @param bool               $sheetTitleForced
     * @param \DateTimeInterface $createdAt
     * @param Group|null         $duplicatedFrom
     */
    public function __construct(
        Event $event,
        User $manager,
        $title,
        bool $sheetTitleForced,
        \DateTimeInterface $createdAt,
        Group $duplicatedFrom = null
    ) {
        $this->event = $event;
        $this->manager = $manager;
        $this->title = $title;
        $this->createdAt = $createdAt;
        $this->sheets = new ArrayCollection();
        $this->duplicatedFrom = $duplicatedFrom;
        $this->sheetTitleForced = $sheetTitleForced;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getManager(): User
    {
        return $this->manager;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return \DateTimeInterface
     */
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

    /**
     * @param string $title
     *
     * @return Group
     */
    public function setTitle($title): Group
    {
        $this->title = $title;

        return $this;
    }

    public function update($title, bool $sheetTitleForced): void
    {
        $this->title = $title;
        $this->sheetTitleForced = $sheetTitleForced;
    }

    /**
     * @param User $manager
     */
    public function setManager(User $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * @return null|Group
     */
    public function getDuplicatedFrom(): ?Group
    {
        return $this->duplicatedFrom;
    }

    public function hasSheetTitleForced(): bool
    {
        return $this->sheetTitleForced;
    }
}
