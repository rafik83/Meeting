<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class EventTranslation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $description;

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
