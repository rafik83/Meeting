<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Exception\Sheet\SheetException;

/**
 * "Fiche de participation".
 */
class Sheet implements BillingInfoInterface, TraceableInterface
{
    const STATE_PENDING   = 'pending';
    const STATE_VALIDATED = 'validated';
    const STATE_ACCEPTED  = 'accepted';

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
     * @var array
     */
    private $packageData;

    /**
     * @var array
     */
    private $billingData;

    /**
     * @var ArrayCollection
     */
    private $orders;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     */
    private $lastLoginAt;

    /**
     * "Etat de la fiche"
     *
     * @var string
     */
    private $state = self::STATE_PENDING;

    /**
     * @var bool
     */
    private $completed = false;

    /**
     * "Suivi commercial"
     *
     * @var Admin
     */
    private $follower;

    /**
     * Sheet constructor.
     *
     * @param Event              $event
     * @param Type               $type
     * @param array              $data
     * @param array              $packageData
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, Type $type, array $data, array $packageData, \DateTimeInterface $createdAt)
    {
        $this->event        = $event;
        $this->type         = $type;
        $this->data         = $data;
        $this->packageData  = $packageData;
        $this->createdAt    = $createdAt;
        $this->lastLoginAt  = $createdAt;
        $this->participants = new ArrayCollection();
        $this->orders       = new ArrayCollection();
        $this->state        = self::STATE_PENDING;
        $this->completed    = false;
    }

    /**
     * Get id.
     *
     * @return mixed
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
     * @param Participant $participant
     *
     * @return Sheet
     */
    public function addParticpant(Participant $participant)
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
     * @return array
     */
    public function getPackageData()
    {
        return $this->packageData;
    }

    /**
     * Set packageData.
     *
     * @param array $packageData
     */
    public function setPackageData(array $packageData)
    {
        $this->packageData = $packageData;
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingData()
    {
        return $this->billingData;
    }

    /**
     * Set billingData.
     *
     * @param array $billingData
     */
    public function setBillingData(array $billingData)
    {
        $this->billingData = $billingData;
    }

    /**
     * Get createdAt
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set lastLoginAt
     *
     * @param \DateTimeInterface $lastLoginAt
     *
     * @return Sheet
     */
    public function setLastLoginAt(\DateTimeInterface $lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    /**
     * Get lastLoginAt
     *
     * @return \DateTimeInterface
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * Get type sheetTemplate.
     *
     * @return array
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
     * @return ArrayCollection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Get the sheet owner
     *
     * @return Participant
     */
    public function getOwner()
    {
        if ($this->participants !== null) {
            foreach ($this->getParticipants() as $participant) {
                if ($participant->isOwner()) {
                    return $participant;
                }
            }
        }

        throw new \RuntimeException('Sheet owner not found.');
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingTemplate()
    {
        return $this->getEvent()->getBillingTemplate();
    }

    /**
     * @param User $user
     *
     * @return Participant|null
     */
    public function getUserParticipant(User $user)
    {
        foreach ($this->participants as $participant) {
            if ($participant->getUser()->getId() === $user->getId()) {
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
        return $this->participants->exists(function ($index, Participant $participant) use ($user) {
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
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return Sheet
     */
    public function markAsIncomplete()
    {
        $this->completed = false;

        return $this;
    }

    /**
     * @return Sheet
     */
    public function markAsComplete()
    {
        $this->completed = true;

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
        return true === $this->completed;
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
}
