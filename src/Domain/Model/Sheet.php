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

/**
 * "Fiche de participation".
 */
class Sheet
{
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
     * @param Event $event
     * @param Type  $type
     * @param array $data
     * @param array $packageData
     */
    public function __construct(Event $event, Type $type, array $data, array $packageData)
    {
        $this->event        = $event;
        $this->type         = $type;
        $this->data         = $data;
        $this->packageData  = $packageData;
        $this->participants = new ArrayCollection();
        $this->orders       = new ArrayCollection();
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
     */
    public function setData(array $data)
    {
        $this->data = $data;
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
     * Get billingData.
     *
     * @return array
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
     * Get type packageTemplate.
     *
     * @return array
     */
    public function getTypeSheetTemplate()
    {
        return $this->getType()->getSheetTemplate();
    }

    /**
     * Get type packageTemplate.
     *
     * @return array
     */
    public function getTypePackageTemplate()
    {
        return $this->getType()->getPackageTemplate();
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
        foreach ($this->getParticipants() as $participant) {
            if ($participant->isOwner()) {
                return $participant;
            }
        }

        throw new \RuntimeException('Sheet owner not found.');
    }
}
