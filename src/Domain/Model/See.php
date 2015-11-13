<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class See
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
     * @var Type
     */
    private $seerType;

    /**
     * @var Category
     */
    private $seerCategory;

    /**
     * @var Type
     */
    private $seeableType;

    /**
     * @var Category
     */
    private $seeableCategory;

    public $what;

    /**
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     */
    public function __construct(Event $event, WhoInterface $seer, WhoInterface $seeable)
    {
        $this->event = $event;

        if ($seer instanceof Type) {
            $this->seerType = $seer;
        } elseif ($seer instanceof Category) {
            $this->seerCategory = $seer;
        } else {
            throw new \InvalidArgumentException(sprintf('Unknow to handle %s', get_class($seer)));
        }

        if ($seeable instanceof Type) {
            $this->seeableType = $seeable;
        } elseif ($seeable instanceof Category) {
            $this->seeableCategory = $seeable;
        } else {
            throw new \InvalidArgumentException(sprintf('Unknow to handle %s', get_class($seeable)));
        }
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
     * Get seerType
     *
     * @return Type
     */
    public function getSeerType()
    {
        return $this->seerType;
    }

    /**
     * Get seerCategory
     *
     * @return Category
     */
    public function getSeerCategory()
    {
        return $this->seerCategory;
    }

    /**
     * Get seeableType
     *
     * @return Type
     */
    public function getSeeableType()
    {
        return $this->seeableType;
    }

    /**
     * Get seeableCategory
     *
     * @return Category
     */
    public function getSeeableCategory()
    {
        return $this->seeableCategory;
    }
}
