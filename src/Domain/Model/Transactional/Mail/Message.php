<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Transactional\Mail;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Message
{
    /** @var int|null */
    private $id;

    /** @var Event */
    private $event;

    /**
     * Transactional Mail Constant
     *
     * @var string
     */
    private $type;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var ArrayCollection of MessageTranslation */
    private $translations;

    /** @var ArrayCollection of Type */
    private $associatedParticipationTypes;

    /**
     * @param Event              $event
     * @param string             $type
     * @param \DateTimeInterface $createdAt
     * @param Type[]             $associatedParticipationTypes
     */
    public function __construct(
        Event $event,
        string $type,
        \DateTimeInterface $createdAt,
        array $associatedParticipationTypes = []
    ) {
        $this->event = $event;
        $this->type = $type;
        $this->createdAt = $createdAt;
        $this->associatedParticipationTypes = $associatedParticipationTypes;
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations(): ArrayCollection
    {
        return $this->translations;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssociatedParticipationTypes(): ArrayCollection
    {
        return $this->associatedParticipationTypes;
    }
}
