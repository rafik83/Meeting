<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class PromotionCode
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
    private $title;

    /**
     * @var ArrayCollection
     */
    private $promotions;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var \DateTimeInterface
     */
    private $validUntil;

    /**
     * @var int
     */
    private $stock;

    /**
     * PromotionCode constructor.
     *
     * @param Event  $event
     * @param string $title
     */
    public function __construct(Event $event, $title)
    {
        $this->title        = $title;
        $this->event        = $event;
        $this->promotions   = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param string $locale
     * @param string $label
     * @param string $description
     *
     * @return PromotionCode
     */
    public function translate($locale, $label, $description)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($label, $description);
        } else {
            $this->translations->add(new PromotionCodeTranslation($this, $locale, $label, $description));
        }

        return $this;
    }

    /**
     * @param \DateTimeInterface $validUntil
     * @param int                $stock
     *
     * @return $this
     */
    public function update($validUntil, $stock)
    {
        $this->validUntil = $validUntil;
        $this->stock      = $stock;

        return $this;
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get promotions
     *
     * @return Promotion[]
     */
    public function getPromotions()
    {
        return $this->promotions->toArray();
    }

    /**
     * Get validUntil
     *
     * @return \DateTimeInterface
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Get label
     *
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getLabel() : null;
    }

    /**
     * Get description
     *
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getDescription() : null;
    }

    /**
     * Get stock
     *
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }
}
