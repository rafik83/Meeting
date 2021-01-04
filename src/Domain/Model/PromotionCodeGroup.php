<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class PromotionCodeGroup
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $title;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var ArrayCollection */
    private $promotionCodes;

    /** @var string|null */
    private $prefix;

    /** @var int|null */
    private $stock;

    /** @var \DateTimeInterface|null */
    private $validUntil;
    /** @var int */
    private $number;

    public function __construct(
        Event $event,
        string $title,
        int $number,
        ?string $prefix,
        ?int $stock,
        ?\DateTimeInterface $validUntil,
        \DateTimeInterface $createdAt
    ) {
        $this->event = $event;
        $this->title = $title;
        $this->number = $number;
        $this->prefix = $prefix;
        $this->stock = $stock;
        $this->validUntil = $validUntil;
        $this->createdAt = $createdAt;
        $this->promotionCodes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    /**
     * @return PromotionCode[]
     */
    public function getPromotionCodes(): array
    {
        return $this->promotionCodes->toArray();
    }

    public function update(
        string $title,
        ?int $stock,
        ?\DateTimeInterface $validUntil
    ) {
        $this->title = $title;
        $this->stock = $stock;
        $this->validUntil = $validUntil;
    }
}
