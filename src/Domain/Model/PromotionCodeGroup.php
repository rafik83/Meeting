<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    public function __construct(Event $event, string $title, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->title = $title;
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

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return PromotionCode[]
     */
    public function getPromotionCodes(): array
    {
        return $this->promotionCodes->toArray();
    }
}
