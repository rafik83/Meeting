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
 * "Evènement"
 */
class Event
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $title;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return EventTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
