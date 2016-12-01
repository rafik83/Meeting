<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class Category
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
     * @var string
     */
    private $picto;

    /**
     * @var int
     */
    private $position;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var string
     */
    private $leftColor;

    /**
     * @var string
     */
    private $rightColor;

    /**
     * Category constructor.
     *
     * @param Event  $event
     * @param string $picto
     * @param int    $position
     * @param string $leftColor
     * @param string $rightColor
     */
    public function __construct(
        Event $event,
        $picto,
        $position,
        $leftColor,
        $rightColor
    ) {
        $this->position     = $position;
        $this->event        = $event;
        $this->picto        = $picto;
        $this->translations = new ArrayCollection();
        $this->leftColor    = $leftColor;
        $this->rightColor   = $rightColor;
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
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set position
     *
     * @param int $position
     *
     * @return Category
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @param string $picto
     */
    public function setPicto($picto)
    {
        $this->picto = $picto;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param CategoryTranslation $categoryTranslation
     */
    public function setTranslation(CategoryTranslation $categoryTranslation)
    {
        $this->translations->set($categoryTranslation->getLocale(), $categoryTranslation);
    }

    /**
     * @return string
     */
    public function getLeftColor()
    {
        return $this->leftColor;
    }

    /**
     * @return string
     */
    public function getRightColor()
    {
        return $this->rightColor;
    }

    /**
     * @param string $leftColor
     */
    public function setLeftColor($leftColor)
    {
        $this->leftColor = $leftColor;
    }

    /**
     * @param string $rightColor
     */
    public function setRightColor($rightColor)
    {
        $this->rightColor = $rightColor;
    }
}
