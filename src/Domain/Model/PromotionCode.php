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
     * @param string             $title
     * @param \DateTimeInterface $validUntil
     * @param int                $stock
     *
     * @return PromotionCode
     */
    public function update($title, $validUntil, $stock)
    {
        $this->title      = $title;
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

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasPromotion(Product $product)
    {
        return $this->promotions->exists(function (Promotion $promotion) use ($product) {
            return $promotion->getProduct() === $product;
        });
    }

    /**
     * @param Product $product
     *
     * @return Promotion
     */
    public function getPromotion(Product $product)
    {
        return $this->promotions->filter(function (Promotion $promotion) use ($product) {
            return $promotion->getProduct() === $product;
        })->first();
    }

    /**
     * @param Product $product
     * @param string  $type
     * @param int     $value
     *
     * @return PromotionCode
     */
    public function setPromotion(Product $product, $type, $value)
    {
        if ($this->hasPromotion($product)) {
            $this->getPromotion($product)->update($type, $value);
        } else {
            $this->promotions->add(new Promotion($this, $product, $type, $value));
        }

        return $this;
    }
}
