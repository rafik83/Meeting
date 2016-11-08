<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Exception\Sheet\SheetException;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

/**
 * "Fiche de participation".
 */
class Sheet implements TraceableInterface
{
    const STATE_PENDING   = 'pending';
    const STATE_VALIDATED = 'validated';
    const STATE_ACCEPTED  = 'accepted';

    /**
     * "Etat de validation de la fiche"
     */
    const STATE_VALIDATION_DRAFT     = 'draft';
    const STATE_VALIDATION_PENDING   = 'pending';
    const STATE_VALIDATION_VALIDATED = 'validated';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var ArrayCollection
     */
    private $participants;

    /**
     * @var array
     */
    private $registrationData;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ArrayCollection
     */
    private $orders;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     */
    private $lastLoginAt;

    /**
     * "Etat de la fiche"
     *
     * @var string
     */
    private $state = self::STATE_PENDING;

    /**
     * "Etat de la validation de la fiche par l'utilisateur"
     *
     * @var string
     */
    private $validationState = self::STATE_VALIDATION_DRAFT;

    /**
     * @var int
     */
    private $completeness = 0;

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * "Suivi commercial"
     *
     * @var Admin
     */
    private $follower;

    /**
     * @var User
     */
    private $owner;

    /**
     * @var bool
     */
    private $inCatalog = false;

    /**
     * @var DateTimeInterface
     */
    private $inCatalogAt;

    /**
     * @var Spot
     */
    private $spot;

    /**
     * Sheet constructor.
     *
     * @param Event              $event
     * @param Type               $type
     * @param array              $data
     * @param User               $owner
     * @param DateTimeInterface $createdAt
     */
    public function __construct(Event $event, Type $type, array $data, User $owner, DateTimeInterface $createdAt)
    {
        $this->event        = $event;
        $this->type         = $type;
        $this->data         = $data;
        $this->owner        = $owner;
        $this->createdAt    = $createdAt;
        $this->lastLoginAt  = $createdAt;
        $this->participants = new ArrayCollection();
        $this->orders       = new ArrayCollection();
        $this->state        = self::STATE_PENDING;
        $this->completeness = 0;
    }

    /**
     * @return array
     */
    public static function getAllStates()
    {
        return [
            self::STATE_ACCEPTED,
            self::STATE_PENDING,
            self::STATE_VALIDATED,
        ];
    }

    /**
     * @return array
     */
    public static function getAllValidationStates()
    {
        return [
            self::STATE_VALIDATION_DRAFT,
            self::STATE_VALIDATION_PENDING,
            self::STATE_VALIDATION_VALIDATED,
        ];
    }

    /**
     * @return int|double
     */
    public function getMaxParticipant()
    {
        return $this->type->getMaxParticipant();
    }

    /**
     * @return bool
     */
    public function canBuyParticipant()
    {
        return $this->getMaxParticipant() > $this->countParticipant();
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
     * @return string
     */
    public function getTraceableName()
    {
        return 'sheet';
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get type.
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get participants.
     *
     * @return ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @return int
     */
    public function countParticipant()
    {
        return $this->participants->count();
    }

    /**
     * @param Participant $participant
     *
     * @return Sheet
     */
    public function addParticipant(Participant $participant)
    {
        $this->participants->add($participant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Sheet
     */
    public function removeParticipant(Participant $participant)
    {
        $this->participants->removeElement($participant);

        return $this;
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
     * @return Sheet
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get Registration Data for the sheet
     *
     * @return array
     */
    public function getRegistrationData()
    {
        return $this->registrationData;
    }

    /**
     * Set Registration Data for the sheet
     *
     * @param array $registrationData
     *
     * @return Sheet
     */
    public function setRegistrationData($registrationData)
    {
        $this->registrationData = $registrationData;

        return $this;
    }

    /**
     * Get packageData.
     *
     * @deprecated
     *
     * @return array
     */
    public function getPackageData()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getValidationState()
    {
        return $this->validationState;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->getType()->getPackage();
    }

    /**
     * @return Product
     */
    public function getPackageParticipant()
    {
        return $this->getPackage()->getParticipant();
    }

    /**
     * Get createdAt
     *
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set lastLoginAt
     *
     * @param DateTimeInterface $lastLoginAt
     *
     * @return Sheet
     */
    public function setLastLoginAt(DateTimeInterface $lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    /**
     * Get lastLoginAt
     *
     * @return DateTimeInterface
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * Get type sheetTemplate.
     *
     * @return SheetTemplate
     */
    public function getTypeSheetTemplate()
    {
        return $this->getType()->getSheetTemplate();
    }

    /**
     * @param Order $order
     *
     * @return Sheet
     */
    public function addOrder(Order $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders->toArray();
    }

    /**
     * @return Order[]
     */
    public function getNotCancelledOrders()
    {
        return array_filter($this->getOrders(), function (Order $order) {
            return !$order->isCancelled();
        });
    }

    /**
     * Get the sheet user owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get the sheet participant owner
     *
     * @return Participant|null
     */
    public function getParticipantOwner()
    {
        return $this->getUserParticipant($this->owner);
    }

    /**
     * @param User $user
     *
     * @return Participant|null
     */
    public function getUserParticipant(User $user)
    {
        foreach ($this->participants as $participant) {
            if ($participant->getUser() === $user) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->owner === $user || $this->participants->exists(function ($index, Participant $participant) use ($user) {
            return $participant->getUser() === $user;
        });
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasParticipant(Participant $participant)
    {
        return $this->participants->contains($participant);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function hasUserParticipant(User $user)
    {
        return null !== $this->getUserParticipant($user);
    }

    /**
     * @return int
     */
    public function countParticipants()
    {
        return $this->participants->count();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isOwner(User $user)
    {
        return $this->owner === $user;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $completeness
     *
     * @return Sheet
     */
    public function setCompleteness($completeness)
    {
        $this->completeness = $completeness;

        return $this;
    }

    /**
     * @return Sheet
     */
    public function markAsValidated()
    {
        $this->state = self::STATE_VALIDATED;

        return $this;
    }

    /**
     * @return Sheet
     */
    public function markAsAccepted()
    {
        $this->state = self::STATE_ACCEPTED;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValidated()
    {
        return self::STATE_VALIDATED === $this->state;
    }

    /**
     * @param bool $state
     *
     * @return Sheet
     */
    public function setEnable($state)
    {
        $this->enable = $state;

        return $this;
    }

    /**
     * @param string $validationState
     *
     * @return Sheet
     */
    public function setValidationState($validationState)
    {
        if (in_array($validationState, self::getAllValidationStates())) {
            $this->validationState = $validationState;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAccepted()
    {
        return self::STATE_ACCEPTED === $this->state;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return 100 === $this->completeness;
    }

    /**
     * @return int
     */
    public function getCompleteness()
    {
        return $this->completeness;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true === $this->enable;
    }

    /**
     * @return bool
     */
    public function isValidationPending()
    {
        return $this->validationState === self::STATE_VALIDATION_PENDING;

    }

    /**
     * @return bool
     */
    public function isValidationDraft()
    {
        return $this->validationState === self::STATE_VALIDATION_DRAFT;
    }

    /**
     * Get follower
     *
     * @return Admin
     */
    public function getFollower()
    {
        return $this->follower;
    }

    /**
     * Assign follower
     *
     * @param Admin $follower
     *
     * @return Sheet
     */
    public function assign(Admin $follower)
    {
        if (!$follower->isOrganizer() && !$follower->isOperator()) {
            throw new SheetException('Follower must be an organizer or operator.');
        }

        $this->follower = $follower;

        return $this;
    }

    /**
     * Get participants users + the owner
     *
     * @return User[]
     */
    public function getUsers()
    {
        /** @var ArrayCollection $users */
        $users = $this->participants->map(function (Participant $participant) {
            return $participant->getUser();
        });

        if (!$users->contains($this->owner)) {
            $users[] = $this->owner;
        }

        return $users->toArray();
    }

    /**
     * @return bool
     */
    public function hasOrders()
    {
        return count($this->getOrders()) > 0;
    }

    /**
     * @return bool
     */
    public function hasNotCancelledOrders()
    {
        return count($this->getNotCancelledOrders()) > 0;
    }

    /**
     * @return bool
     */
    public function isInCatalog()
    {
        return $this->inCatalog;
    }

    /**
     * @param bool $inCatalog
     */
    public function setInCatalog($inCatalog)
    {
        $this->inCatalog = $inCatalog;
    }

    /**
     * @return DateTimeInterface
     */
    public function getInCatalogAt()
    {
        return $this->inCatalogAt;
    }

    /**
     * @param DateTimeInterface $inCatalogAt
     */
    public function setInCatalogAt($inCatalogAt)
    {
        $this->inCatalogAt = $inCatalogAt;
    }

    /**
     * @param Type $type
     */
    public function updateType($type)
    {
        if ($this->type === $type) {
            return;
        }

        $this->type = $type;

        // Set state to pending
        $this->state = self::STATE_PENDING;

        // Remove from catalog
        $this->setInCatalog(false);
    }

    /**
     * @return Spot
     */
    public function getSpot()
    {
        return $this->spot;
    }

    /**
     * @param Spot $spot
     */
    public function setSpot(Spot $spot)
    {
        $this->spot = $spot;
    }

    /**
     * Unassign spot
     */
    public function removeSpot()
    {
        $this->spot = null;
    }

    /**
     * @return Sheet
     */
    public function submitToValidation()
    {
        $this->validationState = self::STATE_VALIDATION_PENDING;

        return $this;
    }
}
