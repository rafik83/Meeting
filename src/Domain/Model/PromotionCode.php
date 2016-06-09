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
     * @var ArrayCollection
     */
    private $products;

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
     * @var string
     */
    private $mode;

    /**
     * @var float
     */
    private $value;

    /**
     * PromotionCode constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event        = $event;
        $this->products     = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     *
     * @return PromotionCode
     */
    public function translate($locale, $title, $description)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($title, $description);
        } else {
            $this->translations->add(new PromotionCodeTranslation($this, $locale, $title, $description));
        }

        return $this;
    }

    /**
     * @param \DateTimeInterface $validUntil
     * @param int                $stock
     * @param string             $mode
     * @param float              $value
     *
     * @return $this
     */
    public function update($validUntil, $stock, $mode, $value)
    {
        $this->validUntil = $validUntil;
        $this->stock      = $stock;
        $this->mode       = $mode;
        $this->value      = $value;

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
     * Get products
     *
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
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
     * Get title
     *
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->translations->containsKey($locale)->getTitle();
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
        return $this->translations->containsKey($locale)->getDescription();
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

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}
