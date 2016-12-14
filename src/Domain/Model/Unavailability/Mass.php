<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Unavailability;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class Mass
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
     * @var \DateTimeInterface
     */
    private $begin;

    /**
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * @var bool
     */
    private $blocking = true;

    /**
     * @var Category
     */
    private $category;

    /**
     * Admin name of the mass unavailability
     *
     * @var string
     */
    private $name;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @param Event              $event
     * @param Category           $category
     * @param string             $name
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $blocking
     */
    public function __construct(
        Event $event,
        Category $category,
        $name,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $blocking
    ) {
        $this->translations = new ArrayCollection();
        $this->event        = $event;
        $this->category     = $category;
        $this->name         = $name;
        $this->begin        = $begin;
        $this->end          = $end;
        $this->blocking     = $blocking;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return boolean
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return MassTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getTitle() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getDescription()
            : '';
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category           $category
     * @param string             $name
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $blocking
     */
    public function update(
        Category $category,
        $name,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $blocking
    ) {
        $this->category = $category;
        $this->name     = $name;
        $this->begin    = $begin;
        $this->end      = $end;
        $this->blocking = $blocking;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     */
    public function updateTranslation($locale, $title, $description)
    {
        $this->createTranslation($locale, $title, $description);
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     */
    public function createTranslation($locale, $title, $description)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($title, $description);
        } else {
            $this->translations->set($locale, new MassTranslation($this, $locale, $title, $description));
        }
    }
}
