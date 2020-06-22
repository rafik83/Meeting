<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Participant".
 */
class Participant implements MailRecipientInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var int
     */
    private $registrationStep;

    /**
     * @var bool
     */
    private $registrationComplete = false;

    /**
     * @var bool
     */
    private $imported = false;

    /**
     * The participant is assigned to accepted request
     *
     * @var bool
     */
    private $hasRequestAssigned = false;

    /**
     * The participant is unavailable or participate to happenings during all the slots of the event
     *
     * @var bool
     */
    private $isFullyUnavailable = false;

    /** @var null|Product */
    private $participantProduct;

    /** @var string */
    private $timezone;

    /** @var \DateTimeInterface */
    private $registrationDate;

    /** @var int */
    private $rank = 0;

    public function __construct(
        Sheet $sheet,
        User $user,
        array $data,
        $active,
        \DateTimeInterface $registrationDate
    ) {
        $this->sheet  = $sheet;
        $this->user   = $user;
        $this->data   = $data;
        $this->active = $active;
        $this->registrationDate = $registrationDate;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getUser()->getLocale();
    }

    /**
     * Get sheet.
     *
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    public function getEvent(): Event
    {
        return $this->sheet->getEvent();
    }

    /**
     * Is owner.
     *
     * @deprecated
     *
     * @return bool
     */
    public function isOwner()
    {
        return false;
    }

    /**
     * Is owner.
     *
     * @return bool
     */
    public function isOwnerParticipant()
    {
        return $this->sheet->getOwner() === $this->user;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param array $data
     *
     * @return Participant
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getRegistrationStep()
    {
        return $this->registrationStep;
    }

    /**
     * @param int $registrationStep
     *
     * @return Participant
     */
    public function setRegistrationStep($registrationStep)
    {
        $this->registrationStep = $registrationStep;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRegistrationComplete()
    {
        return $this->registrationComplete;
    }

    /**
     * @param bool $registrationComplete
     *
     * @return Participant
     */
    public function setRegistrationComplete($registrationComplete)
    {
        $this->registrationComplete = $registrationComplete;

        return $this;
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        return $this->imported;
    }

    /**
     * @param bool $imported
     *
     * @return $this
     */
    public function setImported($imported)
    {
        $this->imported = $imported;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullname()
    {
        return $this->user->getFullname();
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->user->getEmail();
    }

    /**
     * @return bool
     */
    public function hasRequestAssigned()
    {
        return $this->hasRequestAssigned;
    }

    /**
     * @param bool $hasRequestAssigned
     */
    public function setHasRequestAssigned($hasRequestAssigned)
    {
        $this->hasRequestAssigned = $hasRequestAssigned;
    }

    /**
     * @return bool
     */
    public function isFullyUnavailable()
    {
        return $this->isFullyUnavailable;
    }

    /**
     * @param bool $isFullyUnavailable
     */
    public function setFullyUnavailable($isFullyUnavailable)
    {
        $this->isFullyUnavailable = $isFullyUnavailable;
    }

    /**
     * @return null|Product
     */
    public function getParticipantProduct(): ?Product
    {
        return $this->participantProduct;
    }

    public function getIdAndFullName(): string
    {
        return sprintf('%d-%s', $this->getId(), $this->getFullname());
    }

    /**
     * @return bool
     */
    public function hasParticipantProduct(): bool
    {
        return null !== $this->participantProduct;
    }

    /**
     * Call this method via the ParticipantProductSetter service
     *
     * @param Product|null $participantProduct
     */
    public function setParticipantProduct(?Product $participantProduct = null): void
    {
        if (!$participantProduct instanceof Product) {
            $this->participantProduct = null;

            return;
        }

        if (!$participantProduct->isParticipant()) {
            throw new \InvalidArgumentException('Product assigned to Participant must be of type Participant');
        }

        $this->participantProduct = $participantProduct;
    }

    public static function duplicateFrom(Participant $participant, Sheet $sheet, \DateTimeInterface $registrationDate): Participant
    {
        $duplicatedParticipant = new self(
            $sheet,
            $participant->getUser(),
            $participant->getData(),
            $participant->isActive(),
            $registrationDate
        );

        $duplicatedParticipant->setImported(true);

        return $duplicatedParticipant;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getTimezoneFormatted(): ?string
    {
        return str_replace(['_', '/'], [' ', ' / '], $this->timezone);
    }

    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    public function getRegistrationDate(): \DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }
}
