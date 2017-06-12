<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

/**
 * "Entité multi-fiches"
 * sheet_group table
 */
class Group
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var User */
    private $manager;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var string */
    private $title;

    /** @var ArrayCollection of Sheet */
    private $sheets;

    /**
     * @param Event              $event
     * @param User               $manager
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, User $manager, $title, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->manager = $manager;
        $this->title = $title;
        $this->createdAt = $createdAt;
        $this->sheets = new ArrayCollection();
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
     * @return User
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets->toArray();
    }

    /**
     * @param $title
     *
     * @return Group
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param User $manager
     *
     * @return Group
     */
    public function setManager(User $manager)
    {
        $this->manager = $manager;
    }
}
