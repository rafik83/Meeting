<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Catalog\External;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class CatalogVisibility
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var ArrayCollection of Type */
    private $types;

    /** @var ArrayCollection of Category */
    private $categories;

    /**
     * CatalogVisibility constructor.
     * @param Event $event
     * @param array $types
     * @param array $categories
     */
    public function __construct(Event $event, array $types, array $categories)
    {
        $this->event = $event;
        $this->types = $types;
        $this->categories = $categories;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }
}
