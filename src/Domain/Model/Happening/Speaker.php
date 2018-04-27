<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;

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
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var string
     */
    private $organization;

    /**
     * Company logo
     *
     * @var string
     */
    private $logo;

    /**
     * Photo on the speaker
     *
     * @var string
     */
    private $photo;

    /**
     * @var ArrayCollection
     */
    private $talkings;

    /**
     * Speaker constructor.
     *
     * @param Event  $event
     * @param string $firstname
     * @param string $lastname
     * @param string $organization
     * @param string $logo
     * @param string $photo
     */
    public function __construct(Event $event, $firstname, $lastname, $organization, $logo, $photo)
    {
        $this->event        = $event;
        $this->firstname    = $firstname;
        $this->lastname     = $lastname;
        $this->organization = $organization;
        $this->logo         = $logo;
        $this->photo        = $photo;
        $this->talkings     = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
     * Get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get the company logo of the speaker
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Get the photo of the speaker
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Update speaker.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $organization
     * @param string $logo
     * @param string $photo
     *
     * @return Speaker
     */
    public function update($firstname, $lastname, $organization, $logo, $photo)
    {
        $this->firstname    = $firstname;
        $this->lastname     = $lastname;
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

    /**
     * @return Happening[]
     */
    public function getHappenings()
    {
        return $this
            ->talkings
            ->matching(Criteria::create()->orderBy(['position' => 'ASC']))
            ->map(function (Talking $talking) {
                return $talking->getHappening();
            })
            ->toArray();
    }

    /**
     * @param $locale
     *
     * @return string
     */
    public function getPosition($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getPosition() : '';
    }
}
