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
 * "Categorie de participant".
 */
class Category implements WhoInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var ArrayCollection
     */
    private $types;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var Event
     */
    private $event;

    /**
     * Category constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->types = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get types.
     *
     * @return ArrayCollection
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get translations.
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'category';
    }
}
