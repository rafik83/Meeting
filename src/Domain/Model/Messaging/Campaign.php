<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Messaging;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class Campaign
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $title;

    /** @var array */
    private $filters;

    /** @var ArrayCollection|Sheet[] */
    private $sheets;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * @param Event              $event
     * @param string             $title
     * @param array              $filters
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, $title, $filters, \DateTimeInterface $createdAt)
    {
        $this->event     = $event;
        $this->title     = $title;
        $this->filters   = $filters;
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets->toArray();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Sheet $sheet
     */
    public function addSheet(Sheet $sheet)
    {
        $this->sheets->set($sheet->getId(), $sheet);
    }
}
