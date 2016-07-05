<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;
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
     * @var string
     */
    private $code;

    /**
     * @var ArrayCollection of Promotion
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
     * @param Event              $event
     * @param string             $title
     * @param string             $code
     * @param int                $stock
     * @param \DateTimeInterface $validUntil
     */
    public function __construct(Event $event, $title, $code, $stock = null, \DateTimeInterface $validUntil = null)
    {
        $this->event        = $event;
        $this->title        = $title;
        $this->code         = $code;
        $this->stock        = $stock;
        $this->validUntil   = $validUntil;
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
     * @param string             $code
     * @param int                $stock
     * @param \DateTimeInterface $validUntil
     *
     * @return PromotionCode
     */
    public function update($title, $code, $stock = null, \DateTimeInterface $validUntil = null)
    {
        $this->title      = $title;
        $this->code       = $code;
        $this->stock      = $stock;
        $this->validUntil = $validUntil;

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
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
     * Decrease promotion code stock
     *
     * @return PromotionCode
     */
    public function decreaseStock()
    {
        if ($this->stock > 0) {
            $this->stock--;
        }

        return $this;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasPromotion(Product $product)
    {
        return $this->promotions->exists(function ($key, Promotion $promotion) use ($product) {
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

    /**
     * @param Promotion $promotion
     *
     * @return PromotionCode
     */
    public function removePromotion(Promotion $promotion)
    {
        $this->promotions->removeElement($promotion);

        return $this;
    }

    /**
     * @return bool
     */
    public function isSoldOut()
    {
        return $this->stock == 0;
    }

    /**
     * @param DateTimeInterface $datetime
     *
     * @return bool
     */
    public function isOutDated(DateTimeInterface $datetime)
    {
        if (empty($this->validUntil)) {
            return false;
        }

        return $datetime <= $this->validUntil;
    }
}
