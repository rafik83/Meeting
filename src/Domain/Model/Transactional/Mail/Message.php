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
use Doctrine\Common\Collections\Collection;
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
     * @return Collection
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @return Type[]
     */
    public function getAssociatedParticipationTypes(): array
    {
        return $this->associatedParticipationTypes->toArray();
    }

    public function setTranslation(string $locale, string $subject, string $content): void
    {
        $this->translations->set(
            $locale,
            new MessageTranslation(
                $subject,
                $content,
                $locale,
                $this
            )
        );
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    public function getTranslation(string $locale): MessageTranslation
    {
        return $this->translations->get($locale);
    }

    public function translate(string $locale, string $subject, string $content): void
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($subject, $content);
        } else {
            $this->setTranslation($locale, $subject, $content);
        }
    }

    public function update(array $associatedParticipationTypes): void
    {
        $this->associatedParticipationTypes = new ArrayCollection($associatedParticipationTypes);
    }

    public function updateTranslations(array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $this->translate($locale, $translation['subject'], $translation['content']);
        }
    }

    public function getSubject(string $locale): string
    {
        return $this->getTranslation($locale)->getSubject();
    }
}
