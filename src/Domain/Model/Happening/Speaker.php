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
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

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
     * @param string $firstname
     * @param string $lastname
     * @param string $function
     * @param string $organization
     * @param string $logo
     * @param string $photo
     */
    public function __construct(Event $event, $firstname, $lastname, $function, $organization, $logo, $photo)
    {
        $this->event        = $event;
        $this->firstname    = $firstname;
        $this->lastname     = $lastname;
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
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
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
     * Update speaker.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $function
     * @param string $organization
     * @param string $logo
     * @param string $photo
     *
     * @return Speaker
     */
    public function update($firstname, $lastname, $function, $organization, $logo, $photo)
    {
        $this->firstname    = $firstname;
        $this->lastname     = $lastname;
        $this->function     = $function;
        $this->organization = $organization;
        $this->logo         = $logo;
        $this->photo        = $photo;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->firstname . ' ' . strtoupper($this->lastname);
    }
}
