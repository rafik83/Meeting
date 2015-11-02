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
 * "Fiche de participation"
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
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $packageData;

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
        $this->participants = new ArrayCollection();

        $this->setData($data);
        $this->setPackageData($packageData);
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get type
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get participants
     *
     * @return ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return json_decode($this->data, true);
    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data)
    {
        $data = json_encode($data);

        if ($data === null) {
            throw new \InvalidArgumentException('Invalid json data');
        }

        $this->data = $data;
    }

    /**
     * Get packageData
     *
     * @return array
     */
    public function getPackageData()
    {
        return json_decode($this->packageData, true);
    }

    /**
     * Set packageData
     *
     * @param array $packageData
     */
    public function setPackageData($packageData)
    {
        $packageData = json_encode($packageData);

        if ($packageData === null) {
            throw new \InvalidArgumentException('Invalid json data');
        }

        $this->packageData = $packageData;
    }
}
