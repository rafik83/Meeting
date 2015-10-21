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
 * "Type de participation"
 */
class Type
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var string
     */
    private $participantTemplate;

    /**
     * @var string
     */
    private $sheetTemplate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get participantTemplate
     *
     * @return string
     */
    public function getParticipantTemplate()
    {
        return $this->participantTemplate;
    }

    /**
     * Get sheetTemplate
     *
     * @return string
     */
    public function getSheetTemplate()
    {
        return $this->sheetTemplate;
    }
}
