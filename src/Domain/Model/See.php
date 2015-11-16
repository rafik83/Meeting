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

    /**
     * @var array
     */
    private $what;

    /**
     * See constructor.
     *
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     * @param array        $what
     */
    public function __construct(Event $event, WhoInterface $seer, WhoInterface $seeable, array $what)
    {
        $this->event = $event;
        $this->what  = $what;

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

    /**
     * Get what
     *
     * @return array
     */
    public function getWhat()
    {
        return $this->what;
    }

    /**
     * Set what
     *
     * @param array $what
     *
     * @return See
     */
    public function setWhat(array $what)
    {
        $this->what = $what;

        return $this;
    }

    /**
     * @return WhoInterface
     */
    public function getSeer()
    {
        return $this->seerCategory ? : $this->seerType;
    }

    /**
     * @return WhoInterface
     */
    public function getSeeable()
    {
        return $this->seeableCategory ? : $this->seeableType;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->seeableType ? ($this->seerType ? 1 : 2) : ($this->seeableType ? 3 : 4);
    }
}
