<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Event;

class Speaker
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
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $function;

    /**
     * @var string
     */
    private $organization;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var string
     */
    private $photo;

    /**
     * Speaker constructor.
     *
     * @param Event  $event
     * @param string $name
     * @param string $function
     * @param string $organization
     * @param string $logo
     * @param string $photo
     */
    public function __construct(Event $event, $name, $function, $organization, $logo, $photo)
    {
        $this->event        = $event;
        $this->name         = $name;
        $this->function     = $function;
        $this->organization = $organization;
        $this->logo         = $logo;
        $this->photo        = $photo;
    }

    /**
     * Get id
     *
     * @return int
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get function
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param string $name
     * @param string $function
     * @param string $organization
     * @param string $logo
     * @param string $photo
     *
     * @return Speaker
     */
    public function update($name, $function, $organization, $logo, $photo)
    {
        $this->name         = $name;
        $this->function     = $function;
        $this->organization = $organization;
        $this->logo         = $logo;
        $this->photo        = $photo;

        return $this;
    }
}
